<div class="space-y-6">
    <h3 class="text-lg font-medium text-gray-900">Step 6: CO-PO Mapping Matrix</h3>
    
    <!-- Guidance Section -->
    <div class="bg-blue-50 p-4 rounded-md">
        <h4 class="font-medium text-blue-900 mb-2">How to Fill the CO-PO Mapping Matrix</h4>
        <p class="text-sm text-blue-800 mb-3">
            Map each Course Outcome (CO) to Programme Outcomes (POs) and Programme Specific Outcomes (PSOs) 
            based on how strongly the CO contributes to achieving each PO/PSO.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
            <div>
                <strong>Mapping Guidelines:</strong>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li><strong>H (High-3):</strong> CO directly achieves the PO with significant impact</li>
                    <li><strong>M (Medium-2):</strong> CO partially contributes to the PO</li>
                    <li><strong>L (Low-1):</strong> CO has minimal but some contribution to the PO</li>
                    <li><strong>- (None-0):</strong> No correlation between CO and PO</li>
                </ul>
            </div>
            <div>
                <strong>Calculation Method:</strong>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Average = Sum of mapped values / Number of non-zero mappings</li>
                    <li>Target: Each PO should have at least 2-3 CO mappings</li>
                    <li>Average value indicates course-PO alignment strength</li>
                    <li>PSOs should relate to programme-specific skills</li>
                </ul>
            </div>
        </div>
    </div>

    <div x-show="form.course_outcomes.length < 3" class="bg-yellow-50 p-4 rounded-md">
        <p class="text-sm text-yellow-800">
            Please define at least 3 Course Outcomes in Step 3 before filling the mapping matrix.
        </p>
    </div>

    <div x-show="form.course_outcomes.length >= 3" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">CO / PO</th>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="7">Programme Outcomes</th>
                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-indigo-50" colspan="4">Programme Specific Outcomes</th>
                </tr>
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 border-r"></th>
                    <template x-for="i in 7" :key="i">
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-600" x-text="'PO' + i"></th>
                    </template>
                    <template x-for="i in 4" :key="i">
                        <th class="px-2 py-2 text-center text-xs font-medium text-indigo-600 bg-indigo-50" x-text="'PSO' + i"></th>
                    </template>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="(mapping, index) in form.mapping_matrix" :key="index">
                    <tr>
                        <td class="px-3 py-2 text-sm font-medium text-gray-900 border-r whitespace-nowrap">
                            <span x-text="mapping.co_code"></span>
                            <span class="text-xs text-gray-500 block" x-text="form.course_outcomes[index]?.description?.substring(0, 30) + (form.course_outcomes[index]?.description?.length > 30 ? '...' : '')"></span>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po1" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po2" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po3" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po4" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po5" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po6" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2">
                            <select x-model="mapping.po7" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2 bg-indigo-50">
                            <select x-model="mapping.pso1" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2 bg-indigo-50">
                            <select x-model="mapping.pso2" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2 bg-indigo-50">
                            <select x-model="mapping.pso3" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                        <td class="px-1 py-2 bg-indigo-50">
                            <select x-model="mapping.pso4" @change="updatePreview()" class="w-12 text-center text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="-">-</option>
                                <option value="L">L</option>
                                <option value="M">M</option>
                                <option value="H">H</option>
                            </select>
                        </td>
                    </tr>
                </template>
            </tbody>
            <!-- Average Row -->
            <tfoot class="bg-gray-100">
                <tr>
                    <td class="px-3 py-2 text-sm font-bold text-gray-900 border-r">Average</td>
                    <template x-for="po in ['po1', 'po2', 'po3', 'po4', 'po5', 'po6', 'po7']" :key="po">
                        <td class="px-1 py-2 text-center">
                            <span class="text-sm font-semibold rounded px-2 py-1"
                                :class="{
                                    'bg-green-100 text-green-800': getColumnAverage(po) >= 2.5,
                                    'bg-yellow-100 text-yellow-800': getColumnAverage(po) >= 1.5 && getColumnAverage(po) < 2.5,
                                    'bg-orange-100 text-orange-800': getColumnAverage(po) > 0 && getColumnAverage(po) < 1.5,
                                    'bg-gray-100 text-gray-500': getColumnAverage(po) === 0
                                }"
                                x-text="getColumnAverage(po) > 0 ? getColumnAverage(po).toFixed(2) : '-'">
                            </span>
                        </td>
                    </template>
                    <template x-for="pso in ['pso1', 'pso2', 'pso3', 'pso4']" :key="pso">
                        <td class="px-1 py-2 text-center bg-indigo-50">
                            <span class="text-sm font-semibold rounded px-2 py-1"
                                :class="{
                                    'bg-green-100 text-green-800': getColumnAverage(pso) >= 2.5,
                                    'bg-yellow-100 text-yellow-800': getColumnAverage(pso) >= 1.5 && getColumnAverage(pso) < 2.5,
                                    'bg-orange-100 text-orange-800': getColumnAverage(pso) > 0 && getColumnAverage(pso) < 1.5,
                                    'bg-gray-100 text-gray-500': getColumnAverage(pso) === 0
                                }"
                                x-text="getColumnAverage(pso) > 0 ? getColumnAverage(pso).toFixed(2) : '-'">
                            </span>
                        </td>
                    </template>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Validation Summary -->
    <div x-show="form.course_outcomes.length >= 3" class="mt-4">
        <div class="p-4 rounded-md" :class="getMappingCompletionStatus().complete ? 'bg-green-50' : 'bg-yellow-50'">
            <div class="flex items-center">
                <template x-if="getMappingCompletionStatus().complete">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </template>
                <template x-if="!getMappingCompletionStatus().complete">
                    <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </template>
                <div>
                    <span class="text-sm font-medium" :class="getMappingCompletionStatus().complete ? 'text-green-800' : 'text-yellow-800'"
                        x-text="getMappingCompletionStatus().message"></span>
                    <div class="text-xs mt-1" :class="getMappingCompletionStatus().complete ? 'text-green-600' : 'text-yellow-600'">
                        <span x-text="'Mapped COs: ' + getMappingCompletionStatus().mappedCOs + '/' + form.mapping_matrix.length"></span>
                        <span class="mx-2">|</span>
                        <span x-text="'Active POs: ' + getMappingCompletionStatus().activePOs + '/7'"></span>
                        <span class="mx-2">|</span>
                        <span x-text="'Active PSOs: ' + getMappingCompletionStatus().activePSOs + '/4'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="font-medium text-gray-700 mb-2">Legend & Average Interpretation:</h4>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 font-medium mb-1">Mapping Values:</p>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <div><span class="font-medium">H</span> = High (3)</div>
                    <div><span class="font-medium">M</span> = Medium (2)</div>
                    <div><span class="font-medium">L</span> = Low (1)</div>
                    <div><span class="font-medium">-</span> = Not Applicable (0)</div>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-600 font-medium mb-1">Average Color Codes:</p>
                <div class="flex flex-wrap gap-3 text-sm">
                    <div><span class="bg-green-100 text-green-800 px-2 py-0.5 rounded">2.5+</span> Strong</div>
                    <div><span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">1.5-2.49</span> Moderate</div>
                    <div><span class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded">&lt;1.5</span> Weak</div>
                </div>
            </div>
        </div>
    </div>
</div>
