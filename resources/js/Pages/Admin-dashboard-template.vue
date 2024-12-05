<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Users, HandHelping, ShieldCheck, CircleDollarSign, Calendar, DollarSign, TrendingUp, Star, Heart } from "lucide-vue-next";
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, LineElement, PointElement, LinearScale, Title, Tooltip, Legend, CategoryScale } from 'chart.js';

ChartJS.register(LineElement, PointElement, LinearScale, Title, Tooltip, Legend, CategoryScale);

// Mock stats data (replace with real data later)
const stats = {
    users: {
        total: 1234,
        icon: Users,
        label: 'Total Users'
    },
    rentals: {
        total: 567,
        icon: HandHelping,
        label: 'Total Rentals'
    },
    disputes: {
        total: 89,
        resolved: 95,
        icon: ShieldCheck,
        label: 'Disputes'
    },
    transactions: {
        total: 890,
        repeatUsers: 78,
        icon: CircleDollarSign,
        label: 'Total Transactions' 
    }
};

const formatNumber = (value) => {
    return new Intl.NumberFormat("en-US").format(value);
};

// Mock data for revenue overview chart
const revenueData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        {
            label: 'Revenue',
            data: [1200, 1900, 3000, 5000, 2000, 3000, 4000, 5000, 6000, 6500, 8000, 9000],
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            fill: true,
        }
    ]
};

const revenueOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Revenue Overview',
        },
    },
};

// Mock data for top category tool
const topCategoryTool = {
    name: 'Bosch Power Drill',
    category: 'Power Tools',
    averageRental: 15,
    amountListed: 50,
    averageRating: 4.5,
    totalReviews: 120,
    ratings: {
        5: 80,
        4: 25,
        3: 10,
        2: 3,
        1: 2
    },
    reviews: [
        {
            user: 'John Doe',
            rating: 5,
            date: '2024-01-15',
            feedback: 'Great tool, very useful!',
            likes: 10
        }
        
    ]
};

// Disputes by Category
const disputesByCategory = ref([
    { category: 'Late Returns', percentage: 40 },
    { category: 'Damaged Items', percentage: 30 },
    { category: 'Missing Parts', percentage: 20 },
    { category: 'Payment Issues', percentage: 10 },
]);


// Mock data for unresolved disputes
const unresolvedDisputes = ref([
    { id: 1, user: 'Alice', issue: 'Late Return', date: '2024-02-01' },
    { id: 2, user: 'Bob', issue: 'Damaged Item', date: '2024-02-05' },
    { id: 3, user: 'Charlie', issue: 'Missing Parts', date: '2024-02-10' },
]);

// Mock data for dispute statistics
const disputeStatistics = ref({
    totalDisputes: 50,
    resolvedDisputes: 35,
    unresolvedDisputes: 15,
    resolutionRate: 70,
});

// Mock data for users requiring attention
const usersRequiringAttention = ref([
    { id: 1, name: 'Alice', issue: 'Late Payment', date: '2024-02-01' },
    { id: 2, name: 'Bob', issue: 'Account Verification', date: '2024-02-05' },
    { id: 3, name: 'Charlie', issue: 'Profile Incomplete', date: '2024-02-10' },
]);

// Mock data for attention statistics
const attentionStatistics = ref({
    totalUsers: 1000,
    usersRequiringAttention: 50,
    resolvedIssues: 30,
    unresolvedIssues: 20,
});

</script>

<template>
    <Head title="| My Rentals" />

    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-semibold tracking-tight">Welcome Admin!</h2>
        <p class="text-sm text-muted-foreground">
            This is an overview of platform statistics and performance metrics
        </p>
    </div>

    <Separator class="my-6" />

    <!-- Stats Grid -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <!-- Users Card -->
        <Card class="p-6">
            <div class="flex items-center justify-between space-y-0">
                <h3 class="text-sm font-medium tracking-tight text-muted-foreground">
                    {{ stats.users.label }}
                </h3>
                <Users class="h-10 w-10 text-muted-foreground" />
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.users.total) }}
                </div>
            </div>
        </Card>

        <!-- Rentals Card -->
        <Card class="p-6">
            <div class="flex items-center justify-between space-y-0">
                <h3 class="text-sm font-medium tracking-tight text-muted-foreground">
                    {{ stats.rentals.label }}
                </h3>
                <HandHelping class="h-10 w-10 text-muted-foreground" />
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.rentals.total) }}
                </div>
            </div>
        </Card>

        <!-- Disputes Card -->
        <Card class="p-6">
            <div class="flex items-center justify-between space-y-0">
                <h3 class="text-sm font-medium tracking-tight text-muted-foreground">
                    {{ stats.disputes.label }}
                </h3>
                <ShieldCheck class="h-10 w-10 text-muted-foreground" />
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.disputes.total) }}
                </div>
                <p class="text-xs text-muted-foreground">
                    <span class="text-green-500">{{ stats.disputes.resolved }}%</span> resolved
                </p>
            </div>
        </Card>

        <!-- Transactions Card -->
        <Card class="p-6">
            <div class="flex items-center justify-between space-y-0">
                <h3 class="text-sm font-medium tracking-tight text-muted-foreground">
                    {{ stats.transactions.label }}
                </h3>
                <CircleDollarSign class="h-10 w-10 text-muted-foreground" />
            </div>
            <div class="mt-2">
                <div class="text-2xl font-bold">
                    {{ formatNumber(stats.transactions.total) }}
                </div>
                <p class="text-xs text-muted-foreground">
                    <span class="text-green-500">{{ stats.transactions.repeatUsers }}%</span> repeat users
                </p>
            </div>
        </Card>
    </div>

    <Separator class="my-6" />

    <!-- Revenue Overview Card -->
    <Card class="p-6 ">
        <CardHeader>
            <CardTitle>Revenue Overview</CardTitle>
            <CardDescription>Your revenue this year</CardDescription>
        </CardHeader>
        <CardContent>
            <Line :data="revenueData" :options="revenueOptions" />
        </CardContent>
        <CardFooter>
            <div class="flex justify-between items-center mt-3">
                <div class="flex items-center space-x-2">
                    <DollarSign class="h-8 w-8 text-muted-foreground mr-1" />
                    <div>
                        <div class="text-2xl font-bold mt-5">₱{{ formatNumber(50000) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-green-500 ">+10%</span> growth
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <TrendingUp class="h-8 w-8 text-muted-foreground mr-1 ml-6" />
                    <div>
                        <div class="text-2xl font-bold mt-5">₱{{ formatNumber(20000) }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-red-500 mt-4">-5%</span> profit
                        </p>
                    </div>
                </div>
            </div>
        </CardFooter>
    </Card>

    <Separator class="my-6" />

    <!-- Monthly Rental Trends Card -->
    <Card class="p-6">
        <CardHeader>
            <CardTitle>Monthly Rental Trends</CardTitle>
            <CardDescription>Top category</CardDescription>
        </CardHeader>
        <CardContent>
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="text-lg font-semibold">{{ topCategoryTool.name }}</h4>
                    <p class="text-sm text-muted-foreground">{{ topCategoryTool.category }}</p>
                </div>
                <Button variant="outline" size="lg" class="w-7">
                    <Calendar class="h-4 w-4 mr-2" />Date
                </Button>
            </div>
            <img src="https://picsum.photos/200" alt="Tool Image" class="w-35 h-35 object-cover mt-4 rounded-md" />
            <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <h5 class="text-sm font-bold mb-3">Tool Details</h5>
                    <p class="text-sm mt-3 ">Name: {{ topCategoryTool.name }}</p>
                    <p class="text-sm mt-3">Category: {{ topCategoryTool.category }}</p>
                    <p class="text-sm mt-3">Average Rental: ₱{{ formatNumber(topCategoryTool.averageRental) }}</p>
                    <p class="text-sm mt-3">Amount Listed: {{ topCategoryTool.amountListed }}</p>
                    <p class="text-sm mt-3">Average Rating: {{ topCategoryTool.averageRating }} ({{ topCategoryTool.totalReviews }} reviews)</p>
                </div>
                <div>
                    <h5 class="text-sm font-semibold mb-3">Ratings</h5>
                    <div v-for="(count, rating) in topCategoryTool.ratings" :key="rating" class="flex items-center mt-3">
                        <Star class="h-4 w-4 text-yellow-500" />
                        <p class="text-sm ml-2">{{ rating }} stars:  {{ count }}</p>
                    </div>
                </div>
            </div>

            <Separator class="my-6" />

            <card class = "pl-4 pr-4 pb-6">
            <div class="mt-7">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-sm font-semibold">Feedbacks</h5>
                    <Button variant="outline" size="sm">See All</Button>
                </div>

                
                <div v-for="review in topCategoryTool.reviews" :key="review.user" class="mt-2">
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <Avatar class="h-6 w-6 mt-4">
                                <AvatarImage src="https://picsum.photos/200" />
                                <AvatarFallback>{{ review.user.charAt(0) }}</AvatarFallback>
                            </Avatar>
                            <div class="ml-2">
                                <p class="text-sm font-semibold">{{ review.user }}</p>
                                <p class="text-xs text-muted-foreground">{{ review.date }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <Star class="h-4 w-4 text-yellow-500" />
                            <p class="text-sm ml-2">{{ review.rating }}</p>
                        </div>
                    </div>
                    <p class="text-sm mt-5 ml-8">{{ review.feedback }}</p>
                
                </div>
                
            </div> 
        </card>           
        </CardContent>

        <Card class="p-6">
        <CardHeader>
            <CardTitle>Average User Ratings</CardTitle>
            <CardDescription>Overall user satisfaction</CardDescription>
        </CardHeader>
        <CardContent>
            <div class="flex items-center justify-center">
                <div class="text-4xl font-bold">{{ topCategoryTool.averageRating }}</div>
                <Star class="h-6 w-6 text-yellow-500 ml-2" />
            </div>
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-3">
                    <span>5 Stars</span>
                    <span>{{ topCategoryTool.ratings[5] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" :style="{ width: (topCategoryTool.ratings[5] / topCategoryTool.totalReviews) * 100 + '%' }"></div>
                </div>
                <!-- Repeat for other star ratings -->
                <div class="flex justify-between text-sm mt-3 mb-3">
                    <span>4 Stars</span>
                    <span>{{ topCategoryTool.ratings[4] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" :style="{ width: (topCategoryTool.ratings[4] / topCategoryTool.totalReviews) * 100 + '%' }"></div>
                </div>
                <!-- Continue for 3, 2, 1 stars -->
                <div class="flex justify-between text-sm mt-3 mb-3">
                    <span>3 Stars</span>
                    <span>{{ topCategoryTool.ratings[3] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" :style="{ width: (topCategoryTool.ratings[3] / topCategoryTool.totalReviews) * 100 + '%' }"></div>
                </div>
                <div class="flex justify-between text-sm mt-3 mb-3">
                    <span>2 Stars</span>
                    <span>{{ topCategoryTool.ratings[2] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" :style="{ width: (topCategoryTool.ratings[3] / topCategoryTool.totalReviews) * 100 + '%' }"></div>
                </div>
                <div class="flex justify-between text-sm mt-3 mb-3">
                    <span>1 Stars</span>
                    <span>{{ topCategoryTool.ratings[1] }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-yellow-500 h-2.5 rounded-full" :style="{ width: (topCategoryTool.ratings[1] / topCategoryTool.totalReviews) * 100 + '%' }"></div>
                </div>
            </div>
        </CardContent>
    </Card>

    <!-- Most Common Disputes -->
    <Card class="p-6">
        <CardHeader>
            <CardTitle>Most Common Disputes</CardTitle>
            <CardDescription>Disputes by Category</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-for="dispute in disputesByCategory" :key="dispute.category" class="mb-4">
                <div class="flex justify-between text-sm mt-3">
                    <span>{{ dispute.category }}</span>
                    <span>{{ dispute.percentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2 mt-3">
                    <div class="bg-red-500 h-2.5 rounded-full" :style="{ width: dispute.percentage + '%' }"></div>
                </div>
            </div>
        </CardContent>
    </Card>
    </Card>

    <!-- Unresolved Disputes -->
    <Card class="p-6">
            <CardHeader>
                <CardTitle>Unresolved Disputes</CardTitle>
                <CardDescription>List of unresolved disputes</CardDescription>
            </CardHeader>
            <CardContent>
                <ul>
                    <li v-for="dispute in unresolvedDisputes" :key="dispute.id" class="mb-4">
                        <div class="flex justify-between text-sm">
                            <span>{{ dispute.user }}</span>
                            <span>{{ dispute.issue }}</span>
                            <span>{{ dispute.date }}</span>
                        </div>
                    </li>
                </ul>
            </CardContent>

            <Separator class="my-6" />

            <CardHeader>
                <CardTitle>Dispute Statistics</CardTitle>
                <CardDescription>Overview of dispute resolution</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex justify-between text-sm mb-4">
                    <span>Total Disputes</span>
                    <span>{{ disputeStatistics.totalDisputes }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Resolved Disputes</span>
                    <span>{{ disputeStatistics.resolvedDisputes }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Unresolved Disputes</span>
                    <span>{{ disputeStatistics.unresolvedDisputes }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Resolution Rate</span>
                    <span>{{ disputeStatistics.resolutionRate }}%</span>
                </div>
            </CardContent>
        </Card>

        <!-- Users Requiring Attention and Attention Statistics -->
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users Requiring Attention -->
        <Card class="p-6">
            <CardHeader>
                <CardTitle>Users Requiring Attention</CardTitle>
                <CardDescription>List of users needing attention</CardDescription>
            </CardHeader>
            <CardContent>
                <ul>
                    <li v-for="user in usersRequiringAttention" :key="user.id" class="mb-4">
                        <div class="flex justify-between text-sm">
                            <span>{{ user.name }}</span>
                            <span>{{ user.issue }}</span>
                            <span>{{ user.date }}</span>
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <!-- Attention Statistics -->
        <Card class="p-6">
            <CardHeader>
                <CardTitle>Attention Statistics</CardTitle>
                <CardDescription>Overview of attention required</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="flex justify-between text-sm mb-4">
                    <span>Total Users</span>
                    <span>{{ attentionStatistics.totalUsers }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Users Requiring Attention</span>
                    <span>{{ attentionStatistics.usersRequiringAttention }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Resolved Issues</span>
                    <span>{{ attentionStatistics.resolvedIssues }}</span>
                </div>
                <div class="flex justify-between text-sm mb-4">
                    <span>Unresolved Issues</span>
                    <span>{{ attentionStatistics.unresolvedIssues }}</span>
                </div>
            </CardContent>
        </Card>
    </div>

</template>