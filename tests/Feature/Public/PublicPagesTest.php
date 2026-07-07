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
        $this->get(route('about'))->assertOk()->assertSee('Tentang Ular Tangga Statistik Indonesia');
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
