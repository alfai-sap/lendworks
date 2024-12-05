<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { MoreVertical } from "lucide-vue-next";

// Mock data for disputes
const disputes = ref([
    { id: 'D001', username: 'Alice', type: 'Late Return', transactionId: 'T001', dateFiled: '2024-01-01', status: 'Resolved' },
    { id: 'D002', username: 'Bob', type: 'Damaged Item', transactionId: 'T002', dateFiled: '2024-01-05', status: 'Escalated' },
    { id: 'D003', username: 'Charlie', type: 'Missing Parts', transactionId: 'T003', dateFiled: '2024-01-10', status: 'In Progress' },
    // Additional disputes...
]);


const categories = ref(['All Categories', 'Late Return', 'Damaged Item', 'Missing Parts']);
const selectedCategory = ref('All Categories');
const selectedSort = ref('Date Filed');


const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const viewDetails = (dispute) => {
    // Implement view details logic
    console.log('View Details for:', dispute.id);
};

const resolveDispute = (dispute) => {
    // Implement resolve dispute logic
    console.log('Resolve Dispute:', dispute.id);
};



const escalateDispute = (dispute) => {
    // Implement escalate dispute logic
    console.log('Escalate Dispute:', dispute.id);
};

const filterByCategory = (category) => {
    selectedCategory.value = category;
    // Implement category filter logic
};

const sortBy = (sortOption) => {
    selectedSort.value = sortOption;
    // Implement sorting logic
};

</script>

<template>
    <div >
            <CardHeader>
                <CardTitle class="mb-4">Reports and Disputes</CardTitle>
                <CardDescription>Overview of all disputes</CardDescription>
            </CardHeader>
            <CardContent>
                <!-- Filters and Sorting -->
                <div class="mb-4 flex space-x-4">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline">{{ selectedCategory }}</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem v-for="category in categories" :key="category" @click="filterByCategory(category)">
                                {{ category }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline">{{ selectedSort }}</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem @click="sortBy('Date Filed')">Date Filed</DropdownMenuItem>
                            <DropdownMenuItem @click="sortBy('Status')">Status</DropdownMenuItem>
                            <DropdownMenuItem @click="sortBy('Username')">Username</DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <!-- Disputes Table -->
                <div class="overflow-x-auto mt-8">
                    <table class="min-w-full ">
                        <thead>
                            <tr >
                                <th class="py-2 px-4 border-b text-left">Dispute ID</th>
                                <th class="py-2 px-4 border-b text-left">Username</th>
                                <th class="py-2 px-4 border-b text-left">Type of Report</th>
                                <th class="py-2 px-4 border-b text-left">Transaction ID</th>
                                <th class="py-2 px-4 border-b text-left">Date Filed</th>
                                <th class="py-2 px-4 border-b text-left">Status</th>
                                <th class="py-2 px-4 border-b text-center">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dispute in disputes" :key="dispute.id" >
                                <td class="py-2 px-4 border-b">{{ dispute.id }}</td>
                                <td class="py-2 px-4 border-b">{{ dispute.username }}</td>
                                <td class="py-2 px-4 border-b">{{ dispute.type }}</td>
                                <td class="py-2 px-4 border-b">{{ dispute.transactionId }}</td>
                                <td class="py-2 px-4 border-b">{{ dispute.dateFiled }}</td>
                                <td class="py-2 px-4 border-b">{{ dispute.status }}</td>
                                <td class="py-2 px-4 border-b text-center">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                <MoreVertical class="w-5 h-5" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent class="z-50 min-w-[8rem]">
                                            <DropdownMenuItem @click="viewDetails(dispute)">View Details</DropdownMenuItem>
                                            <DropdownMenuItem @click="resolveDispute(dispute)">Resolve</DropdownMenuItem>
                                            <DropdownMenuItem @click="escalateDispute(dispute)">Escalate</DropdownMenuItem>
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
                        Showing {{ disputes.length }} results
                    </div>
                    <div class="text-sm text-muted-foreground">
                        <!-- Placeholder for pagination controls -->
                        Page 1 of 1
                    </div>
                </div>
            </CardContent>
    
    </div>
</template>
<style scoped>
/* Add any necessary styles */
</style>