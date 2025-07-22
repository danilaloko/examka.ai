<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Отчет о практике',
            'Курсовая работа',
            'Доклад',
            'Эссе',
            'Реферат',
            'Научная статья',
            'Диплом',
            'Контрольные работы',
            'Доклад',
            'ВКР',
            'Диссертация',
            'Сочинение',
            'ВАК',
            'Автореферат',
            'Диплом MBA',
            'Кандидатская диссертация',
            'Магистреская диссертация',
            'Рецензия',
            'Исследовательские работы',
            'Выводы',
            'Введение',
            'Домашняя работа',
            'Научно исследовательская работа',
        ];

        foreach ($types as $type) {
            DocumentType::create([
                'name' => $type,
                'slug' => Str::slug($type),
                'description' => "Тип документа: {$type}"
            ]);
        }
    }
} 