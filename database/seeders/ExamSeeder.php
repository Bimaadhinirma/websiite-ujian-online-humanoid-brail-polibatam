<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Period;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AnswerKey;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'username' => 'admin',
            'nama' => 'Administrator',
            'password' => bcrypt('admin123'),
            'role' => 1
        ]);

        // Create Participants
        User::create([
            'username' => 'participant1',
            'nama' => 'Participant Satu',
            'password' => bcrypt('participant123'),
            'role' => 2
        ]);

        User::create([
            'username' => 'participant2',
            'nama' => 'Participant Dua',
            'password' => bcrypt('participant123'),
            'role' => 2
        ]);

        // Create Period
        $period = Period::create([
            'name' => 'Seleksi Magang BFC 2025',
            'start' => now(),
            'end' => now()->addDays(7),
            'status' => true,
            'show_result' => false,
            'show_grade' => false
        ]);

        // Category 1: Programming
        $categoryProgramming = Category::create([
            'period_id' => $period->id,
            'name' => 'Programming',
            'order' => 1,
            'descriptions' => 'Kategori soal tentang pemrograman dasar'
        ]);

        // Question 1 - Programming (Options)
        $q1 = Question::create([
            'category_id' => $categoryProgramming->id,
            'question' => 'Apa kepanjangan dari HTML?',
            'order' => 1,
            'type' => 'options',
            'grade' => 10
        ]);

        $q1opt1 = QuestionOption::create(['question_id' => $q1->id, 'option' => 'Hyper Text Markup Language', 'order' => 1]);
        $q1opt2 = QuestionOption::create(['question_id' => $q1->id, 'option' => 'High Tech Modern Language', 'order' => 2]);
        $q1opt3 = QuestionOption::create(['question_id' => $q1->id, 'option' => 'Home Tool Markup Language', 'order' => 3]);
        
        AnswerKey::create([
            'question_id' => $q1->id,
            'question_option_id' => $q1opt1->id,
            'key' => (string)$q1opt1->id
        ]);

        // Question 2 - Programming (Input)
        $q2 = Question::create([
            'category_id' => $categoryProgramming->id,
            'question' => 'Sebutkan bahasa pemrograman yang paling populer untuk web development!',
            'order' => 2,
            'type' => 'input',
            'grade' => 15
        ]);

        AnswerKey::create([
            'question_id' => $q2->id,
            'question_option_id' => null,
            'key' => 'JavaScript'
        ]);

        // Category 2: Robotics
        $categoryRobotics = Category::create([
            'period_id' => $period->id,
            'name' => 'Robotics',
            'order' => 2,
            'descriptions' => 'Kategori soal tentang dasar robotika'
        ]);

        // Question 3 - Robotics (Options)
        $q3 = Question::create([
            'category_id' => $categoryRobotics->id,
            'question' => 'Apa kepanjangan dari PWM?',
            'order' => 1,
            'type' => 'options',
            'grade' => 10
        ]);

        $q3opt1 = QuestionOption::create(['question_id' => $q3->id, 'option' => 'Pulse Width Modulation', 'order' => 1]);
        $q3opt2 = QuestionOption::create(['question_id' => $q3->id, 'option' => 'Power Wave Modification', 'order' => 2]);
        $q3opt3 = QuestionOption::create(['question_id' => $q3->id, 'option' => 'Programmable Wire Module', 'order' => 3]);
        
        AnswerKey::create([
            'question_id' => $q3->id,
            'question_option_id' => $q3opt1->id,
            'key' => (string)$q3opt1->id
        ]);

        // Question 4 - Robotics (Input)
        $q4 = Question::create([
            'category_id' => $categoryRobotics->id,
            'question' => 'Sebutkan satu jenis sensor yang digunakan untuk mendeteksi jarak!',
            'order' => 2,
            'type' => 'input',
            'grade' => 20
        ]);

        AnswerKey::create([
            'question_id' => $q4->id,
            'question_option_id' => null,
            'key' => 'Ultrasonic'
        ]);

        // Category 3: Logic & Math
        $categoryLogic = Category::create([
            'period_id' => $period->id,
            'name' => 'Logic & Mathematics',
            'order' => 3,
            'descriptions' => 'Kategori soal tentang logika dan matematika'
        ]);

        // Question 5 - Logic (Options)
        $q5 = Question::create([
            'category_id' => $categoryLogic->id,
            'question' => 'Berapa hasil dari 15 + 25 * 2?',
            'order' => 1,
            'type' => 'options',
            'grade' => 15
        ]);

        $q5opt1 = QuestionOption::create(['question_id' => $q5->id, 'option' => '65', 'order' => 1]);
        $q5opt2 = QuestionOption::create(['question_id' => $q5->id, 'option' => '80', 'order' => 2]);
        $q5opt3 = QuestionOption::create(['question_id' => $q5->id, 'option' => '55', 'order' => 3]);
        
        AnswerKey::create([
            'question_id' => $q5->id,
            'question_option_id' => $q5opt1->id,
            'key' => (string)$q5opt1->id
        ]);

        // Question 6 - Logic (Input)
        $q6 = Question::create([
            'category_id' => $categoryLogic->id,
            'question' => 'Jika A = 1, B = 2, C = 3, ... maka nilai dari ROBOT adalah?',
            'order' => 2,
            'type' => 'input',
            'grade' => 20
        ]);

        AnswerKey::create([
            'question_id' => $q6->id,
            'question_option_id' => null,
            'key' => '55'
        ]);

        $this->command->info('Exam data seeded successfully!');
    }
}
