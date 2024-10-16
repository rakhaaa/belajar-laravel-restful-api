<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contact = Contact::where('first_name', 'test')->first();
        Address::create([
            'street' => 'test',
            'city' => 'test',
            'province' => 'test',
            'country' => 'test',
            'postal_code' => '12345',
            'contact_id' => $contact->id
        ]);
    }
}
