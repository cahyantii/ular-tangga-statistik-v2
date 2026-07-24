<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_landing_page_can_be_rendered(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Ular Tangga Statistik Indonesia');
    }

    public function test_about_page_can_be_rendered(): void
    {
        // Dipecah jadi dua assertSee(), bukan satu judul utuh - markup
        // aslinya (public/about.blade.php) membungkus "Statistik Indonesia"
        // dalam <span> terpisah dari "Tentang Ular Tangga" di baris
        // sebelumnya (dua baris judul beda warna), jadi satu assertSee()
        // gabungan gagal mendeteksi teks yang justru tampil BENAR ke
        // pengguna (cuma terpisah tag, bukan rusak).
        $this->get(route('about'))->assertOk()
            ->assertSee('Tentang Ular Tangga')
            ->assertSee('Statistik Indonesia');
    }

    public function test_how_to_play_page_can_be_rendered(): void
    {
        $this->get(route('how-to-play'))->assertOk()->assertSee('Cara Bermain');
    }

    public function test_faq_page_can_be_rendered(): void
    {
        $this->get(route('faq'))->assertOk()->assertSee('Pertanyaan yang Sering Diajukan');
    }
}
