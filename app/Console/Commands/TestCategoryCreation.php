<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Traits\HasRoles;

class TestCategoryCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:category-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test category creation with validation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing category creation...');

        // Verificar si hay un usuario autenticado
        if (!auth()->check()) {
            $this->error('No hay usuario autenticado');
            return 1;
        }

        $this->info('Usuario autenticado: ' . auth()->user()->name);

        // Test data
        $testData = [
            'name' => 'Test Category ' . time(), // Nombre único
            'description' => 'Test Description',
            'color' => '#FF0000',
            'type' => 'element'
        ];

        // Validate data
        $validator = Validator::make($testData, [
            'name' => 'required|string|max:255|unique:category,name',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'type' => 'required|string|in:element,kit,component',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('- ' . $error);
            }
            return 1;
        }

        $this->info('Validation passed!');

        // Try to create category
        try {
            $category = Category::create([
                'name' => $testData['name'],
                'description' => $testData['description'],
                'color' => $testData['color'],
                'type' => $testData['type'],
                'active' => true,
                'created_by' => auth()->id(),
            ]);

            $this->info('Category created successfully!');
            $this->info('ID: ' . $category->id);
            $this->info('Name: ' . $category->name);
            $this->info('Type: ' . $category->type);

            // Clean up
            $category->delete();
            $this->info('Test category deleted.');

        } catch (\Exception $e) {
            $this->error('Error creating category: ' . $e->getMessage());
            return 1;
        }

        $this->info('Test completed successfully!');
        return 0;
    }
}
