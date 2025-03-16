<?php $this->layout('layout', ['title' => $project->name]) ?>

<div x-data="projectManager(<?= htmlspecialchars(json_encode([
    'projectId' => $project->id,
    'projectName' => $project->name,
    'basePath' => $basePath
]), ENT_QUOTES, 'UTF-8') ?>)" 
    class="space-y-6">
    
    <!-- Mobile-friendly header section -->
    <div class="flex sm:flex-row flex-col justify-between items-start sm:items-center gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-4">
                <a href="<?= $basePath ?>/" class="flex-shrink-0 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="font-bold text-gray-900 text-xl sm:text-3xl truncate"><?= $this->e($project->name) ?></h1>
            </div>
            <?php if ($project->description): ?>
                <p class="mt-2 text-gray-600 line-clamp-2"><?= $this->e($project->description) ?></p>
            <?php endif ?>
        </div>
        
        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <!-- Project Actions Dropdown -->
            <div class="relative sm:flex-initial flex-1">
                <button @click="dropdownOpen = !dropdownOpen" 
                        class="inline-flex justify-center items-center bg-white hover:bg-gray-50 shadow-sm px-3 sm:px-4 py-2 border border-gray-300 rounded-md w-full sm:w-auto font-medium text-gray-700 text-sm">
                    <span>Project Actions</span>
                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="dropdownOpen" 
                     @click.away="dropdownOpen = false"
                     class="right-0 z-50 absolute bg-white shadow-lg mt-2 rounded-md w-48">
                    <div class="py-1">
                        <button @click="dropdownOpen = false; editProject()" 
                                class="block hover:bg-gray-100 px-4 py-2 w-full text-gray-700 text-sm text-left">
                            Edit Project
                        </button>
                        <button @click="dropdownOpen = false; cloneProject()" 
                                class="block hover:bg-gray-100 px-4 py-2 w-full text-gray-700 text-sm text-left">
                            Clone Project
                        </button>
                        <button @click="dropdownOpen = false; confirmDelete()" 
                                class="block hover:bg-red-50 px-4 py-2 w-full text-red-600 text-sm text-left">
                            Delete Project
                        </button>
                    </div>
                </div>
            </div>
            <a href="<?= $basePath ?>/projects/<?= $this->e($project->id) ?>/tables/new" 
               class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-indigo-600 hover:bg-indigo-700 px-3 sm:px-4 py-2 rounded-md text-white text-sm transition-colors">
                <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>New Table</span>
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="z-50 fixed inset-0 flex justify-center items-center bg-gray-500 bg-opacity-75"
         x-cloak>
        <div class="bg-white shadow-xl p-6 rounded-lg w-full max-w-md">
            <h3 class="mb-4 font-medium text-gray-900 text-lg">Delete Project</h3>
            <p class="mb-4 text-gray-500">
                Are you sure you want to delete "<span x-text="projectName"></span>"? 
                This will permanently delete the project and all its tables. This action cannot be undone.
            </p>
            <div class="flex justify-end space-x-3">
                <button @click="cancelDelete()" 
                        class="px-4 py-2 font-medium text-gray-700 hover:text-gray-900 text-sm">
                    Cancel
                </button>
                <button @click="deleteProject()" 
                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md font-medium text-white text-sm">
                    Delete Project
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($tables)): ?>
        <div class="bg-white shadow py-12 rounded-lg text-center">
            <h3 class="font-medium text-gray-900 text-lg">No tables yet</h3>
            <p class="mt-2 text-gray-500">Create your first dice table to get started!</p>
        </div>
    <?php else: ?>
        <div class="gap-6 grid md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($tables as $table): ?>
                <div class="bg-white shadow hover:shadow-md rounded-lg transition-shadow">
                    <a href="<?= $basePath ?>/tables/<?= $this->e($table->id) ?>" class="block p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-xl"><?= $this->e($table->name) ?></h3>
                                <div class="mt-2 font-medium text-indigo-600">
                                    <?= $this->e($table->dice_expression) ?>
                                </div>
                                <?php if ($table->description): ?>
                                    <p class="mt-2 text-gray-600"><?= $this->e($table->description) ?></p>
                                <?php endif ?>
                                <div class="mt-4 text-gray-500 text-sm">
                                    <?= count($table->entries) ?> entries
                                </div>
                            </div>
                            <div class="relative ml-4">
                                <button @click.prevent="openTableMenu(<?= $table->id ?>)"
                                        class="hover:bg-gray-100 p-2 rounded-full">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                                <div x-show="activeTableMenu === <?= $table->id ?>"
                                     @click.away="activeTableMenu = null"
                                     class="right-0 z-50 absolute bg-white shadow-lg mt-2 rounded-md w-48">
                                    <div class="py-1">
                                        <button @click.prevent="editTable(<?= $table->id ?>)" 
                                                class="block hover:bg-gray-100 px-4 py-2 w-full text-gray-700 text-sm text-left">
                                            Edit Details
                                        </button>
                                        <button @click.prevent="cloneTable(<?= $table->id ?>)" 
                                                class="block hover:bg-gray-100 px-4 py-2 w-full text-gray-700 text-sm text-left">
                                            Clone Table
                                        </button>
                                        <button @click.prevent="confirmDeleteTable(<?= $table->id ?>, '<?= addslashes($table->name) ?>')" 
                                                class="block hover:bg-red-50 px-4 py-2 w-full text-red-600 text-sm text-left">
                                            Delete Table
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <!-- Delete Table Confirmation Modal -->
    <div x-show="showDeleteTableModal" 
         class="z-50 fixed inset-0 flex justify-center items-center bg-gray-500 bg-opacity-75"
         x-cloak>
        <div class="bg-white shadow-xl p-6 rounded-lg w-full max-w-md">
            <h3 class="mb-4 font-medium text-gray-900 text-lg">Delete Table</h3>
            <p class="mb-4 text-gray-500">
                Are you sure you want to delete "<span x-text="tableToDelete.name"></span>"? 
                This will permanently delete the table and all its entries. This action cannot be undone.
            </p>
            <div class="flex justify-end space-x-3">
                <button @click="cancelDeleteTable()" 
                        class="px-4 py-2 font-medium text-gray-700 hover:text-gray-900 text-sm">
                    Cancel
                </button>
                <button @click="deleteTable()" 
                        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md font-medium text-white text-sm">
                    Delete Table
                </button>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
function projectManager(config) {
    return {
        projectId: config.projectId,
        projectName: config.projectName,
        basePath: config.basePath,
        showDeleteModal: false,
        dropdownOpen: false,
        showDeleteTableModal: false,
        tableToDelete: { id: null, name: '' },
        activeTableMenu: null,

        openTableMenu(tableId) {
            this.activeTableMenu = this.activeTableMenu === tableId ? null : tableId;
        },

        editProject() {
            window.location.href = `${this.basePath}/projects/${this.projectId}/edit`;
        },

        cloneProject() {
            window.location.href = `${this.basePath}/projects/${this.projectId}/clone`;
        },

        confirmDelete() {
            this.showDeleteModal = true;
        },

        async deleteProject() {
            try {
                const response = await fetch(`${window.location.origin}${this.basePath}/api/projects/${this.projectId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to delete project');
                }

                window.location.href = this.basePath;
            } catch (error) {
                this.showDeleteModal = false;
                alert('Error deleting project: ' + error.message);
            }
        },

        cancelDelete() {
            this.showDeleteModal = false;
        },

        // Table management methods
        editTable(tableId) {
            window.location.href = `${this.basePath}/tables/${tableId}/edit`;
        },

        cloneTable(tableId) {
            window.location.href = `${this.basePath}/tables/${tableId}/clone`;
        },

        confirmDeleteTable(tableId, tableName) {
            this.tableToDelete = { id: tableId, name: tableName };
            this.showDeleteTableModal = true;
            this.activeTableMenu = null;
        },

        async deleteTable() {
            try {
                const response = await fetch(`${window.location.origin}${this.basePath}/api/tables/${this.tableToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to delete table');
                }

                // Reload the page to show updated table list
                window.location.reload();
            } catch (error) {
                this.showDeleteTableModal = false;
                alert('Error deleting table: ' + error.message);
            }
        },

        cancelDeleteTable() {
            this.showDeleteTableModal = false;
            this.tableToDelete = { id: null, name: '' };
        }
    };
}
</script>
<?php $this->end() ?> 