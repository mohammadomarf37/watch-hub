<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();

        $addresses = [
            [
                'address_line1' => '123 Main Street',
                'address_line2' => 'Apt 4B',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'US',
                'phone' => '+12125551234',
            ],
            [
                'address_line1' => '456 Park Avenue',
                'address_line2' => null,
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90001',
                'country' => 'US',
                'phone' => '+13235551234',
            ],
            [
                'address_line1' => '789 London Road',
                'address_line2' => 'Flat 2',
                'city' => 'London',
                'state' => 'Greater London',
                'postal_code' => 'SW1A 1AA',
                'country' => 'UK',
                'phone' => '+442079255555',
            ],
            [
                'address_line1' => '10 Downing Street',
                'address_line2' => null,
                'city' => 'London',
                'state' => 'Greater London',
                'postal_code' => 'SW1A 2AA',
                'country' => 'UK',
                'phone' => '+442079254444',
            ],
            [
                'address_line1' => '15-B Johar Town',
                'address_line2' => null,
                'city' => 'Lahore',
                'state' => 'Punjab',
                'postal_code' => '54000',
                'country' => 'PK',
                'phone' => '+9242111222333',
            ],
        ];

        foreach ($users as $index => $user) {
            $addressCount = rand(1, 2);

            for ($i = 0; $i < $addressCount; $i++) {
                $address = $addresses[array_rand($addresses)];

                Address::create([
                    'user_id' => $user->id,
                    'address_line1' => $address['address_line1'],
                    'address_line2' => $address['address_line2'],
                    'city' => $address['city'],
                    'state' => $address['state'],
                    'postal_code' => $address['postal_code'],
                    'country' => $address['country'],
                    'phone' => $address['phone'],
                    'is_default' => $i === 0,
                    'address_type' => 'shipping',
                ]);
            }
        }
    }
}
