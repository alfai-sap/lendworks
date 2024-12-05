<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { MoreVertical } from "lucide-vue-next";

// Mock data for tools
const tools = ref([
    { id: 1, name: 'Bosch Power Drill', category: 'Power Tools', status: 'Active', dateListed: '2024-01-01', location: 'New York', ratePerDay: 15 },
    { id: 2, name: 'Makita Circular Saw', category: 'Power Tools', status: 'Inactive', dateListed: '2024-01-05', location: 'Los Angeles', ratePerDay: 20 },
    { id: 3, name: 'DeWalt Hammer Drill', category: 'Power Tools', status: 'Active', dateListed: '2024-01-10', location: 'Chicago', ratePerDay: 25 },
    // Additional tools...
]);

const searchQuery = ref('');
const selectedCategory = ref('All Categories');
const selectedStatus = ref('All Statuses');

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const searchTools = () => {
    // Implement search logic here
    console.log('Searching for:', searchQuery.value);
};

const filterByCategory = (category) => {
    selectedCategory.value = category;
    // Implement category filter logic here
};

const filterByStatus = (status) => {
    selectedStatus.value = status;
    // Implement status filter logic here
};

const viewDetails = (tool) => {
    // Implement view details logic
    console.log('View Details for:', tool.id);
};

const editTool = (tool) => {
    // Implement edit tool logic
    console.log('Edit Tool:', tool.id);
};

const deleteTool = (tool) => {
    // Implement delete tool logic
    console.log('Delete Tool:', tool.id);
};
</script>

<template>
    <!-- Manage Tools -->
    <div>
        <CardHeader>
            <CardTitle class="mb-4">Manage Tools</CardTitle>
            <CardDescription>Manage platform tools</CardDescription>
        </CardHeader>
        <CardContent>
            <!-- Filters and Search Bar -->
            <div class="mb-4 flex space-x-4">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">{{ selectedCategory }}</Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem @click="filterByCategory('All Categories')">All Categories</DropdownMenuItem>
                        <DropdownMenuItem @click="filterByCategory('Power Tools')">Power Tools</DropdownMenuItem>
                        <DropdownMenuItem @click="filterByCategory('Hand Tools')">Hand Tools</DropdownMenuItem>
                        <!-- Add more categories as needed -->
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline">{{ selectedStatus }}</Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuItem @click="filterByStatus('All Statuses')">All Statuses</DropdownMenuItem>
                        <DropdownMenuItem @click="filterByStatus('Active')">Active</DropdownMenuItem>
                        <DropdownMenuItem @click="filterByStatus('Inactive')">Inactive</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <div class="flex flex-1">
                    <Input v-model="searchQuery" placeholder="Search tools..." class="w-full" />
                    <Button @click="searchTools" class="ml-2">Search</Button>
                </div>
            </div>

            <!-- Tools Table -->
            <div class="overflow-x-auto mt-5">
                <table class="min-w-full mt-5">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Tool ID</th>
                            <th class="py-2 px-4 border-b text-left">Tools</th>
                            <th class="py-2 px-4 border-b text-left">Category</th>
                            <th class="py-2 px-4 border-b text-left">Active Status</th>
                            <th class="py-2 px-4 border-b text-left">Date Listed</th>
                            <th class="py-2 px-4 border-b text-left">Location</th>
                            <th class="py-2 px-4 border-b text-left">Rate Per Day</th>
                            <th class="py-2 px-4 border-b text-center">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="tool in tools" :key="tool.id">
                            <td class="py-2 px-4 border-b">{{ tool.id }}</td>
                            <td class="py-2 px-4 border-b flex items-center">
                                <img :src="tool.image" alt="Tool Image" class="w-10 h-10 rounded-full mr-2" />
                                {{ tool.name }}
                            </td>
                            <td class="py-2 px-4 border-b">{{ tool.category }}</td>
                            <td class="py-2 px-4 border-b">{{ tool.status }}</td>
                            <td class="py-2 px-4 border-b">{{ tool.dateListed }}</td>
                            <td class="py-2 px-4 border-b">{{ tool.location }}</td>
                            <td class="py-2 px-4 border-b">₱{{ formatNumber(tool.ratePerDay) }}</td>
                            <td class="py-2 px-4 border-b text-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon">
                                            <MoreVertical class="w-5 h-5" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-44">
                                        <DropdownMenuItem @click="viewDetails(tool)">
                                            View Details
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="editTool(tool)">
                                            Edit Tool
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteTool(tool)" class="text-destructive">
                                            Delete Tool
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4">
                <div class="text-sm text-muted-foreground">
                    Showing {{ tools.length }} results
                </div>
                <div class="text-sm text-muted-foreground">
                    <!-- Placeholder for pagination controls -->
                    Page 1 of 1
                </div>
            </div>
        </CardContent>
    </div>
</template>