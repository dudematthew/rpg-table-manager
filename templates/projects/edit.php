<?php $this->layout('layout', ['title' => 'Edit Project']) ?>

<div class="mx-auto max-w-2xl">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="font-bold text-gray-900 text-3xl">Edit Project</h1>
        </div>
    </div>

    <form action="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>" method="POST" class="space-y-6 bg-white shadow p-6 rounded-lg">
        <div>
            <label for="name" class="block font-medium text-gray-700 text-sm">Project Name</label>
            <input type="text" name="name" id="name" required
                   value="<?= $this->e($project->name) ?>"
                   class="block shadow-sm mt-1 border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500 w-full">
        </div>

        <div>
            <label for="description" class="block font-medium text-gray-700 text-sm">Description (optional)</label>
            <textarea name="description" id="description" rows="4"
                      class="block shadow-sm mt-1 border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500 w-full"><?= $this->e($project->description) ?></textarea>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>" 
               class="px-4 py-2 font-medium text-gray-700 hover:text-gray-900 text-sm">Cancel</a>
            <button type="submit" 
                    class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-white">
                Save Changes
            </button>
        </div>
    </form>
</div> 