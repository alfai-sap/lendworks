<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";
import { Clock, Wallet, ArrowRight, Calendar, DollarSign, FileText, MoreVertical } from "lucide-vue-next";

// Mock data for transactions
const transactions = ref([
    {
        id: 1,
        toolName: 'Power Drill',
        toolImage: '/images/power-drill.jpg',
        renter: 'Alice',
        lender: 'John',
        status: 'Completed',
        date: '2024-01-01',
        amount: 1500
    },
    {
        id: 2,
        toolName: 'Circular Saw',
        toolImage: '/images/circular-saw.jpg',
        renter: 'Bob',
        lender: 'Jane',
        status: 'Pending',
        date: '2024-01-05',
        amount: 2000
    },
    {
        id: 3,
        toolName: 'Hammer',
        toolImage: '/images/hammer.jpg',
        renter: 'Charlie',
        lender: 'Steve',
        status: 'Refunded',
        date: '2024-01-10',
        amount: 2500
    },

    {
        id: 4,
        toolName: 'Power Drill',
        toolImage: '/images/power-drill.jpg',
        renter: 'Alice',
        lender: 'John',
        status: 'Completed',
        date: '2024-01-01',
        amount: 1500
    },
    {
        id: 5,
        toolName: 'Circular Saw',
        toolImage: '/images/circular-saw.jpg',
        renter: 'Bob',
        lender: 'Jane',
        status: 'Pending',
        date: '2024-01-05',
        amount: 2000
    },
    {
        id: 6,
        toolName: 'Hammer',
        toolImage: '/images/hammer.jpg',
        renter: 'Charlie',
        lender: 'Steve',
        status: 'Refunded',
        date: '2024-01-10',
        amount: 2500
    },
    // Additional transactions...
]);

const searchQuery = ref('');
const selectedStatus = ref('All Statuses');

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const searchTransactions = () => {
    // Implement search logic here
    console.log('Searching for:', searchQuery.value);
};

const filterByStatus = (status) => {
    selectedStatus.value = status;
    // Implement status filter logic here
};
</script>

<template>
    <!-- Manage Transactions -->
    <div>
            <CardHeader>
                <CardTitle class="mb-4">Manage Transactions</CardTitle>
                <CardDescription>Overview of all transactions</CardDescription>
            </CardHeader>
            <CardContent>
                <!-- Transaction Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-7">
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Ongoing Rentals</CardTitle>
                            <CardDescription>{{ formatNumber(10) }} rentals</CardDescription>
                        </div>
                        <Clock class="w-8 h-8 text-muted-foreground" />
                    </Card>
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Payments on Hold</CardTitle>
                            <CardDescription>₱{{ formatNumber(5000) }}</CardDescription>
                        </div>
                        <Wallet class="w-8 h-8 text-muted-foreground" />
                    </Card>
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Released Payments</CardTitle>
                            <CardDescription>₱{{ formatNumber(15000) }}</CardDescription>
                        </div>
                        <ArrowRight class="w-8 h-8 text-muted-foreground" />
                    </Card>
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Overdue Fees</CardTitle>
                            <CardDescription>₱{{ formatNumber(2000) }}</CardDescription>
                        </div>
                        <Calendar class="w-8 h-8 text-muted-foreground" />
                    </Card>
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Refunds Issued</CardTitle>
                            <CardDescription>₱{{ formatNumber(3000) }}</CardDescription>
                        </div>
                        <DollarSign class="w-8 h-8 text-muted-foreground" />
                    </Card>
                    <Card class="p-4 flex items-center justify-between">
                        <div>
                            <CardTitle class = "mb-5">Dispute Payments Issued</CardTitle>
                            <CardDescription>₱{{ formatNumber(1000) }}</CardDescription>
                        </div>
                        <FileText class="w-8 h-8 text-muted-foreground" />
                    </Card>
                </div>

                <!-- Filters and Search Bar -->
                <div class="mb-4 flex space-x-4 ">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline">{{ selectedStatus }}</Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent>
                            <DropdownMenuItem @click="filterByStatus('All Statuses')">
                                All Statuses
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="filterByStatus('Completed')">
                                Completed
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="filterByStatus('Pending')">
                                Pending
                            </DropdownMenuItem>
                            <DropdownMenuItem @click="filterByStatus('Refunded')">
                                Refunded
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <div class="flex flex-1 ">
                        <Input v-model="searchQuery" placeholder="Search transactions..." class="w-full" />
                        <Button @click="searchTransactions" class="ml-2">Search</Button>
                    </div>
                </div>

                <!-- Transactions Table -->
                <card class = "mt-10">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b text-left">Rental ID</th>
                                <th class="py-2 px-4 border-b text-left">Tool Name</th>
                                <th class="py-2 px-4 border-b text-left">Renter</th>
                                <th class="py-2 px-4 border-b text-left">Lender</th>
                                <th class="py-2 px-4 border-b text-left">Transaction Status</th>
                                <th class="py-2 px-4 border-b text-left">Transaction Date</th>
                                <th class="py-2 px-4 border-b text-left">Amount</th>
                                <th class="py-2 px-4 border-b text-center">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="transaction in transactions" :key="transaction.id">
                                <td class="py-2 px-4 border-b">{{ transaction.id }}</td>
                                <td class="py-2 px-4 border-b flex items-center gap-2">
                                    <img :src="transaction.toolImage" alt="Tool Image" class="w-9 h-9 rounded object-cover" />
                                    <span>{{ transaction.toolName }}</span>
                                </td>
                                <td class="py-2 px-4 border-b">{{ transaction.renter }}</td>
                                <td class="py-2 px-4 border-b">{{ transaction.lender }}</td>
                                <td class="py-2 px-4 border-b">{{ transaction.status }}</td>
                                <td class="py-2 px-4 border-b">{{ transaction.date }}</td>
                                <td class="py-2 px-4 border-b">₱{{ formatNumber(transaction.amount) }}</td>
                                <td class="py-2 px-4 border-b text-center">
                                    <Button variant="ghost" size="icon">
                                        <MoreVertical class="w-5 h-5" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </card>


                <!-- Pagination -->
                <div class="flex justify-between items-center mt-4">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ transactions.length }} results
                    </div>
                    <div class="text-sm text-muted-foreground">
                        <!-- Placeholder for pagination controls -->
                        Page 1 of 1
                    </div>
                </div>
            </CardContent>
        
    </div>
</template>