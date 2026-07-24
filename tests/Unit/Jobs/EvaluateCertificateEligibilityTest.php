<?php

namespace Tests\Unit\Jobs;

use App\Actions\GenerateCertificateNumber;
use App\Actions\GenerateVerificationCode;
use App\Jobs\EvaluateCertificateEligibility;
use App\Models\Certificate;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvaluateCertificateEligibilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeJob(User $user): EvaluateCertificateEligibility
    {
        return new EvaluateCertificateEligibility($user);
    }

    private function runJob(EvaluateCertificateEligibility $job): void
    {
        $job->handle(
            new LearningProgressService(),
            new PlayerStatsService(),
            new GenerateCertificateNumber(),
            new GenerateVerificationCode(),
        );
    }

    private function makeEligibleUser(): User
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();

        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 10,
            'total_benar' => 9,
            'total_salah' => 1,
            'accuracy' => 90,
        ]);

        $sesi = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id]);

        return $user;
    }

    public function test_generates_a_certificate_and_pdf_when_all_three_conditions_are_met(): void
    {
        Storage::fake('local');

        $user = $this->makeEligibleUser();

        $this->runJob($this->makeJob($user));

        $certificate = Certificate::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($certificate);
        $this->assertSame('penguasaan_statistik_dasar', $certificate->jenis_sertifikat);
        $this->assertNotNull($certificate->verification_code);
        Storage::disk('local')->assertExists($certificate->file_path);
    }

    public function test_does_not_generate_when_not_all_categories_are_completed(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        // Dua kategori aktif, tapi hanya satu yang pernah dijawab.
        KategoriMateri::factory()->create();
        $kategoriDijawab = KategoriMateri::factory()->create();
        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategoriDijawab->id,
            'total_dijawab' => 5,
            'total_benar' => 5,
            'total_salah' => 0,
            'accuracy' => 100,
        ]);
        $sesi = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id]);

        $this->runJob($this->makeJob($user));

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_does_not_generate_when_accuracy_is_below_80_percent(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();
        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 10,
            'total_benar' => 5,
            'total_salah' => 5,
            'accuracy' => 50,
        ]);
        $sesi = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id]);

        $this->runJob($this->makeJob($user));

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_does_not_generate_when_no_game_has_ever_finished(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();
        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 10,
            'total_benar' => 9,
            'total_salah' => 1,
            'accuracy' => 90,
        ]);

        $this->runJob($this->makeJob($user));

        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_does_not_generate_a_second_certificate_for_the_same_user(): void
    {
        Storage::fake('local');

        $user = $this->makeEligibleUser();
        $this->runJob($this->makeJob($user));
        $this->assertDatabaseCount('certificates', 1);
        $firstNumber = Certificate::first()->nomor_sertifikat;

        $this->runJob($this->makeJob($user));

        $this->assertDatabaseCount('certificates', 1);
        $this->assertSame($firstNumber, Certificate::first()->nomor_sertifikat);
    }

    /**
     * Jaring pengaman utama untuk race condition (dua job untuk user yang
     * sama lolos pengecekan exists() nyaris bersamaan) ada di level
     * database, bukan di PHP - tidak bisa disimulasikan lewat dua proses
     * sungguhan di PHPUnit (satu proses, sinkron). Tes ini memverifikasi
     * DUA hal yang bersama-sama membuat perbaikannya benar: (1) constraint
     * unique di migrasi add_unique_constraint_to_certificates_user_id
     * benar-benar mencegah dua baris certificates dengan user_id sama, dan
     * (2) exception yang dilempar persis UniqueConstraintViolationException
     * (tipe yang ditangkap EvaluateCertificateEligibility::handle()).
     */
    public function test_database_rejects_a_second_certificate_row_for_the_same_user_id(): void
    {
        $user = User::factory()->create();
        Certificate::factory()->create(['user_id' => $user->id]);

        $this->expectException(UniqueConstraintViolationException::class);

        Certificate::factory()->create(['user_id' => $user->id]);
    }
}
