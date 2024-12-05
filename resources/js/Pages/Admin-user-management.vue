<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Users, HandHelping, ShieldCheck, CircleDollarSign, Calendar, DollarSign, TrendingUp, Star, Heart, MoreVertical } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuPortal,
    DropdownMenuSeparator
} from "@/components/ui/dropdown-menu";

// Mock data for users
const users = ref([
    { id: 1100, profilePicture: 'https://picsum.photos/50', username: 'Alice', email: 'alice@example.com', status: 'Verified', dateRegistered: '2024-01-01', location: 'Tumaga', contactNumber: '123-456-7890' },
    { id: 1200, profilePicture: 'https://picsum.photos/50', username: 'Bob', email: 'bob@example.com', status: 'Pending', dateRegistered: '2024-01-05', location: 'Baliwasan', contactNumber: '098-765-4321' },
    { id: 1300, profilePicture: 'https://picsum.photos/50', username: 'Charlie', email: 'charlie@example.com', status: 'Verified', dateRegistered: '2024-01-10', location: 'Mercedes', contactNumber: '555-555-5555' },
    { id: 1100, profilePicture: 'https://picsum.photos/50', username: 'Alice', email: 'alice@example.com', status: 'Verified', dateRegistered: '2024-01-01', location: 'Tumaga', contactNumber: '123-456-7890' },
    { id: 1200, profilePicture: 'https://picsum.photos/50', username: 'Bob', email: 'bob@example.com', status: 'Pending', dateRegistered: '2024-01-05', location: 'Baliwasan', contactNumber: '098-765-4321' },
    { id: 1300, profilePicture: 'https://picsum.photos/50', username: 'Charlie', email: 'charlie@example.com', status: 'Verified', dateRegistered: '2024-01-10', location: 'Mercedes', contactNumber: '555-555-5555' },
    { id: 1100, profilePicture: 'https://picsum.photos/50', username: 'Alice', email: 'alice@example.com', status: 'Verified', dateRegistered: '2024-01-01', location: 'Tumaga', contactNumber: '123-456-7890' },
    { id: 1200, profilePicture: 'https://picsum.photos/50', username: 'Bob', email: 'bob@example.com', status: 'Pending', dateRegistered: '2024-01-05', location: 'Baliwasan', contactNumber: '098-765-4321' },
    { id: 1300, profilePicture: 'https://picsum.photos/50', username: 'Charlie', email: 'charlie@example.com', status: 'Verified', dateRegistered: '2024-01-10', location: 'Mercedes', contactNumber: '555-555-5555' },
    // Additional users...
]);

const searchQuery = ref('');

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

const searchUsers = () => {
    // Implement search logic here
    console.log('Searching for:', searchQuery.value);
};

// Add state for dropdown visibility
const openDropdowns = ref({});

const toggleDropdown = (userId) => {
    openDropdowns.value[userId] = !openDropdowns.value[userId];
};

</script>

<template>
    <!-- User Management -->
    <div class="mt-4">
        <Card class="p-6">
            <CardHeader>
                <CardTitle class="mb-4">User Management</CardTitle>
                <CardDescription>Manage platform users</CardDescription>
            </CardHeader>
            <CardContent>
                <!-- Search Bar -->
                <div class="mb-4 flex">
                    <Input v-model="searchQuery" placeholder="Search users..." class="w-full" />
                    <Button @click="searchUsers" class="ml-2">Search</Button>
                </div>

                <!-- User Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full" >
                        <thead>
                            <tr >
                                <th class="py-2 px-4 border-b text-left">UserID</th>
                                <th class="py-2 px-4 border-b text-left">Username</th>
                                <th class="py-2 px-4 border-b text-left">Email</th>
                                <th class="py-2 px-4 border-b text-left">Status</th>
                                <th class="py-2 px-4 border-b text-left">Date Registered</th>
                                <th class="py-2 px-4 border-b text-left">Location</th>
                                <th class="py-2 px-4 border-b text-left">Contact Number</th>
                                <th class="py-2 px-4 border-b text-center">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td class="py-2 px-4 border-b">{{ user.id }}</td>
                                <td class="py-2 px-4 border-b flex items-center">
                                    <img :src="user.profilePicture" alt="Profile Picture" class="w-8 h-8 rounded-full mr-2" />
                                    {{ user.username }}
                                </td>
                                <td class="py-2 px-4 border-b">{{ user.email }}</td>
                                <td class="py-2 px-4 border-b">{{ user.status }}</td>
                                <td class="py-2 px-4 border-b">{{ user.dateRegistered }}</td>
                                <td class="py-2 px-4 border-b">{{ user.location }}</td>
                                <td class="py-2 px-4 border-b">{{ user.contactNumber }}</td>
                                <td class="py-2 px-4 border-b text-center relative">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger class="outline-none">
                                            <Button variant="ghost" size="icon" class="h-8 w-8 p-0">
                                                <MoreVertical class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        
                                        <DropdownMenuPortal>
                                            <DropdownMenuContent 
                                                class="z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md animate-in" 
                                                align="end"
                                            >
                                                <DropdownMenuItem class="cursor-pointer">
                                                    View User
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="cursor-pointer">
                                                    Update User Details
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem class="cursor-pointer">
                                                    Verify User
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="cursor-pointer text-destructive">
                                                    Delete User
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenuPortal>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-4">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ users.length }} results
                    </div>
                    <div class="text-sm text-muted-foreground">
                        <!-- Placeholder for pagination controls -->
                        Page 1 of 1
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>

<style>
.dropdown-content {
    position: absolute;
    right: 0;
    margin-top: 0.5rem;
    z-index: 50;
}
</style>