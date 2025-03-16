<?php $this->layout('layout', ['title' => 'Projects']) ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="font-bold text-gray-900 text-3xl">Projects</h1>
        <a href="<?= $basePath ?>/projects/new" class="bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-md text-white transition-colors">
            New Project
        </a>
    </div>

    <?php if (empty($projects)): ?>
        <div class="bg-white shadow py-12 rounded-lg text-center">
            <h3 class="font-medium text-gray-900 text-lg">No projects yet</h3>
            <p class="mt-2 text-gray-500">Get started by creating your first project!</p>
        </div>
    <?php else: ?>
        <div class="gap-6 grid md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($projects as $project): ?>
                <a href="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>" 
                   class="block bg-white shadow hover:shadow-md p-6 rounded-lg transition-shadow">
                    <h3 class="font-semibold text-gray-900 text-xl"><?= $this->e($project->name) ?></h3>
                    <?php if ($project->description): ?>
                        <p class="mt-2 text-gray-600"><?= $this->e($project->description) ?></p>
                    <?php endif ?>
                    <div class="mt-4 text-gray-500 text-sm">
                        <?= count($project->tables) ?> tables
                    </div>
                </a>
            <?php endforeach ?>
        </div>
    <?php endif ?>
</div> 