<?php $this->layout('layout', ['title' => 'Iframe Test - ' . $table->name]) ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <a href="<?= $basePath ?>/tables/<?= $this->e($table->id) ?>" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="font-bold text-gray-900 text-3xl">Iframe Test</h1>
            <span class="font-medium text-gray-600"><?= $this->e($table->name) ?></span>
        </div>
    </div>

    <div class="space-y-6 bg-white shadow p-6 rounded-lg">
        <div>
            <h2 class="mb-2 font-medium text-gray-900 text-lg">Embed Code</h2>
            <div class="bg-gray-50 p-4 rounded-md">
                <code class="font-mono text-gray-800 text-sm">&lt;iframe src="<?= $currentUrl ?>" width="100%" height="400" frameborder="0"&gt;&lt;/iframe&gt;</code>
            </div>
            <p class="mt-2 text-gray-500 text-sm">Copy this code to embed the table in your website or application.</p>
        </div>

        <div>
            <h2 class="mb-2 font-medium text-gray-900 text-lg">Preview</h2>
            <div class="border border-gray-200 rounded-md">
                <iframe src="<?= $currentUrl ?>" width="100%" height="400" frameborder="0"></iframe>
            </div>
        </div>

        <div>
            <h2 class="mb-2 font-medium text-gray-900 text-lg">Customization</h2>
            <div x-data="{ width: '100%', height: '400' }" class="space-y-4">
                <div class="gap-4 grid grid-cols-2">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700 text-sm">Width</label>
                        <input type="text" x-model="width" 
                               class="form-input w-full"
                               placeholder="e.g., 100%, 500px">
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700 text-sm">Height</label>
                        <input type="text" x-model="height" 
                               class="form-input w-full"
                               placeholder="e.g., 400px">
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-md">
                    <code class="font-mono text-gray-800 text-sm" x-text="'<iframe src=\'<?= $currentUrl ?>\' width=\'' + width + '\' height=\'' + height + '\' frameborder=\'0\'></iframe>'"></code>
                </div>

                <div class="border border-gray-200 rounded-md">
                    <div x-html="'<iframe src=\'<?= $currentUrl ?>\' width=\'' + width + '\' height=\'' + height + '\' frameborder=\'0\'></iframe>'"></div>
                </div>
            </div>
        </div>
    </div>
</div> 