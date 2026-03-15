<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 5: Learning Resources</h3>

    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="font-medium text-gray-700">Self Learning</h4>
        <p class="text-xs text-gray-500 mt-1">Assignment / activities for specific learning / skill development / online courses / micro projects.</p>
        <textarea x-model="form.self_learning" @input="updatePreview()" rows="3"
            class="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
            placeholder="Example: Not Applicable"></textarea>
    </div>

    <div class="bg-gray-50 p-4 rounded-md">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-700">Special Instructional Strategies</h4>
            <button type="button" @click="addInstructionalStrategy()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Strategy</button>
        </div>

        <template x-for="(strategy, index) in form.special_instructional_strategies" :key="index">
            <div class="flex items-start gap-2 mb-2">
                <span class="mt-2 text-sm font-medium text-gray-500" x-text="(index + 1) + '.'"></span>
                <input type="text" x-model="form.special_instructional_strategies[index]" @input="updatePreview()" @keydown.enter.prevent
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    placeholder="Instructional strategy">
                <button type="button" @click="removeInstructionalStrategy(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
            </div>
        </template>

        <div x-show="form.special_instructional_strategies.length === 0" class="text-center py-4 text-gray-500 text-sm">
            No strategies added yet.
        </div>
    </div>
    
    <!-- Books -->
    <div class="bg-gray-50 p-4 rounded-md">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-700">Books</h4>
            <button type="button" @click="addBook()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Book</button>
        </div>
        
        <template x-for="(book, index) in form.books" :key="index">
            <div class="bg-white p-3 rounded-md mb-3 border">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600" x-text="'Book ' + (index + 1)"></span>
                    <button type="button" @click="removeBook(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-600">Title</label>
                        <input type="text" x-model="book.title" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Author</label>
                        <input type="text" x-model="book.author" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Edition</label>
                        <input type="text" x-model="book.edition" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="e.g., 3rd Edition">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Publication</label>
                        <input type="text" x-model="book.publication" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">ISBN</label>
                        <input type="text" x-model="book.isbn" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </template>
        
        <div x-show="form.books.length === 0" class="text-center py-4 text-gray-500 text-sm">
            No books added yet.
        </div>
    </div>

    <!-- Software/Websites -->
    <div class="bg-gray-50 p-4 rounded-md">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-700">Software & Websites</h4>
            <button type="button" @click="addSoftware()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add</button>
        </div>
        
        <template x-for="(software, index) in form.software_websites" :key="index">
            <div class="bg-white p-3 rounded-md mb-3 border">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600" x-text="'Resource ' + (index + 1)"></span>
                    <button type="button" @click="removeSoftware(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600">Name/Tool</label>
                        <input type="text" x-model="software.name" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">URL</label>
                        <input type="url" x-model="software.url" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-600">Description</label>
                        <input type="text" x-model="software.description" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </template>
        
        <div x-show="form.software_websites.length === 0" class="text-center py-4 text-gray-500 text-sm">
            No software or websites added yet.
        </div>
    </div>

    <!-- Equipment List -->
    <div class="bg-gray-50 p-4 rounded-md">
        <div class="flex justify-between items-center mb-3">
            <h4 class="font-medium text-gray-700">Equipment List</h4>
            <button type="button" @click="addEquipment()" class="text-sm text-indigo-600 hover:text-indigo-800">+ Add Equipment</button>
        </div>
        
        <template x-for="(equipment, index) in form.equipment_list" :key="index">
            <div class="bg-white p-3 rounded-md mb-3 border">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600" x-text="'Item ' + equipment.s_no"></span>
                    <button type="button" @click="removeEquipment(index)" class="text-red-500 hover:text-red-700 text-sm">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600">Equipment Name</label>
                        <input type="text" x-model="equipment.name" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600">Specifications</label>
                        <input type="text" x-model="equipment.specifications" @input="updatePreview()" @keydown.enter.prevent
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </template>
        
        <div x-show="form.equipment_list.length === 0" class="text-center py-4 text-gray-500 text-sm">
            No equipment added yet.
        </div>
    </div>
</div>
