<?php $this->layout('layout', ['title' => 'New Table']) ?>

<div class="mx-auto max-w-2xl">
    <div class="mb-8">
        <h1 class="font-bold text-gray-900 text-3xl">Create New Table</h1>
        <p class="mt-2 text-gray-600">Project: <?= $this->e($project->name) ?></p>
    </div>

    <form action="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>/tables" method="POST" class="space-y-6 bg-white shadow p-6 rounded-lg">
        <div>
            <label for="name" class="block font-medium text-gray-700 text-sm">Table Name</label>
            <input type="text" name="name" id="name" required
                   class="block shadow-sm mt-1 border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500 w-full">
        </div>

        <div>
            <label for="dice_expression" class="block font-medium text-gray-700 text-sm">Dice Expression</label>
            <input type="text" name="dice_expression" id="dice_expression" required placeholder="e.g., d20, 3d6, 2d4+1"
                   class="block shadow-sm mt-1 border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500 w-full">
            <p class="mt-1 text-gray-500 text-sm">Supported formats: d20, 3d6, 2d4+1, etc.</p>
        </div>

        <div>
            <label for="description" class="block font-medium text-gray-700 text-sm">Description (optional)</label>
            <textarea name="description" id="description" rows="4"
                      class="block shadow-sm mt-1 border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500 w-full"></textarea>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>" 
               class="px-4 py-2 font-medium text-gray-700 hover:text-gray-900 text-sm">Cancel</a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-white">
                Create Table
            </button>
        </div>
    </form>
</div> 