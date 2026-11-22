<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Scope;
use App\Models\ServiceClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ServicesClientSeeder extends Seeder
{



    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceClients = [
            "auth-vault" => [
                'name' => "AuthVault",
                "scopes" =>["audit:events:write"]
            ],
            "student-portal" => [
                "name" => "StudentPortal",
                "scopes" => ["audit:events:write","internal:users:basic-info:read"]
            ],
            "audit-logs" => [
                'name' => "AuditLog",
                'scopes'=>[],
                
            ],
            "support-desk" => [
                "name" => "SupportDesk",
                "scopes"=> ["audit:events:write","internal:security-status:read"]
            ],
            "file-store" => [
                "name" => "FileStore",
                "scopes"=> ["audit:events:write"]
            ],
            "library-core" => [
                "name" => "LibraryCore",
                "scopes" => ["audit:events:write","internal:student-status:read","internal:student-debt:write"]
            ],
        ];


        foreach ($serviceClients as $clientId => $data) {
            $plain = Str::random(64);
            $client = ServiceClient::firstOrCreate(['client_id' => $clientId],['client_secret'=>Hash::make($plain),'name' => $data['name']]);
            if($client->wasRecentlyCreated) {
                $this->command->info("{$clientId} - {$plain}");
            }
            $ids = Scope::whereIn('name', $data['scopes'])->pluck('id');
            $client->scopes()->sync($ids);
        }
    }
}
