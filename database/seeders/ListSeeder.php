<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contact = Contact::where('first_name', 'test')->first();
        for ($i = 1; $i <= 20; $i++) {
            Address::create([
                'street' => 'test' . $i,
                'city' => 'test' . $i,
                'province' => 'test' . $i,
                'country' => 'test' . $i,
                'postal_code' => '12345' . $i,
                'contact_id' => $contact->id
            ]);
        }
    }
}
