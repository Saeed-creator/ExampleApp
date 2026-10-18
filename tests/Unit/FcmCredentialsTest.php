<?php

namespace Tests\Unit;

use App\Services\FcmService;
use Tests\TestCase;

class FcmCredentialsTest extends TestCase
{
    private function key(): array
    {
        return ['project_id' => 'demo', 'client_email' => 'x@demo.iam.gserviceaccount.com', 'private_key' => "-----BEGIN PRIVATE KEY-----\nabc\n-----END PRIVATE KEY-----\n"];
    }

    public function test_not_configured_without_file_or_variable(): void
    {
        config(['services.fcm.credentials' => '/nope.json', 'services.fcm.credentials_json' => null]);
        $this->assertFalse((new FcmService())->isConfigured());
    }

    public function test_raw_json_variable_is_enough(): void
    {
        config(['services.fcm.credentials' => '/nope.json', 'services.fcm.credentials_json' => json_encode($this->key())]);
        $this->assertTrue((new FcmService())->isConfigured());
    }

    public function test_base64_json_variable_is_enough(): void
    {
        config(['services.fcm.credentials' => '/nope.json', 'services.fcm.credentials_json' => base64_encode(json_encode($this->key()))]);
        $this->assertTrue((new FcmService())->isConfigured());
    }

    public function test_garbage_variable_is_ignored(): void
    {
        config(['services.fcm.credentials' => '/nope.json', 'services.fcm.credentials_json' => 'not json at all']);
        $this->assertFalse((new FcmService())->isConfigured());
    }
}
