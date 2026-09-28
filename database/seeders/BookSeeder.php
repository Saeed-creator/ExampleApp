<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Add multiple book records
        Book::create([
            'title' => 'The Great Gatsby',
            'author' => 'F. Scott Fitzgerald',
            'description' => 'A classic novel set in the 1920s, exploring themes of wealth and society.',
            'year_published' => 1925,
        ]);

        Book::create([
            'title' => 'To Kill a Mockingbird',
            'author' => 'Harper Lee',
            'description' => 'A novel about racial injustice in the American South, seen through the eyes of a young girl.',
            'year_published' => 1960,
        ]);

        Book::create([
            'title' => '1984',
            'author' => 'George Orwell',
            'description' => 'A dystopian novel about a totalitarian regime and the dangers of surveillance and propaganda.',
            'year_published' => 1949,
        ]);
        Book::create([
            'title' => 'ASPU',
            'author' => 'F. Scott Fitzgerald',
            'description' => 'A classic novel sets in the 1920s, exploring themes of wealth and society.',
            'year_published' => 1925,
        ]);
    }
}
