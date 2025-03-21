<script setup>
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import {
	Select,
	SelectContent,
	SelectItem,
	SelectTrigger,
	SelectValue,
} from "@/components/ui/select";
import StatCard from "@/Components/StatCard.vue";
import RentalCard from "@/Components/RentalCard.vue";
import { ref, computed } from "vue";
import { formatLabel } from "@/lib/formatters";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
	groupedListings: Object,
	rentalStats: Object,
	cancellationReasons: Array,
	userRole: String,
});

const selectedTab = ref("pending");

const tabs = [
	{ id: "pending", label: "Pending" },
	{ id: "approved", label: "Approved" },
	{ id: "payments", label: "Payments" },
	{ id: "to_handover", label: "To Handover" },
	{ id: "active", label: "Active" },
	{ id: "overdue", label: "Overdue" },
	{ id: "paid_overdue", label: "Paid Overdue" },
	{ id: "pending_return", label: "Return Pending" },
	{ id: "return_scheduled", label: "Return Scheduled" },
	{ id: "pending_return_confirmation", label: "Return Confirmation" },
	{ id: "pending_final_confirmation", label: "Final Confirmation" },
	{ id: "disputed", label: "In Dispute" },
	{ id: "completed", label: "Completed" },
	{ id: "rejected", label: "Rejected" },
	{ id: "cancelled", label: "Cancelled" },
];

const handleValueChange = (value) => {
	selectedTab.value = value;
};
</script>

<template>
	<Head title="My Rentals" />
	<div class="space-y-6">
		<!-- Header -->
		<div class="sm:flex-row sm:items-center sm:justify-between flex flex-col gap-4">
			<div class="space-y-1">
				<h2 class="text-2xl font-semibold tracking-tight">My Rentals</h2>
				<p class="text-muted-foreground text-sm">View and manage your rental requests</p>
			</div>
		</div>

		<!-- Stats Cards -->
		<div class="sm:grid-cols-3 lg:grid-cols-6 grid grid-cols-2 gap-3">
			<StatCard
				v-for="(count, status) in rentalStats"
				:key="status"
				:label="formatLabel(status)"
				:value="count"
			/>
		</div>

		<!-- Tabs for lg+ screens -->
		<div class="lg:block hidden">
			<Tabs v-model="selectedTab" class="w-full" @update:modelValue="handleValueChange">
				<TabsList class="flex flex-wrap items-center gap-2 p-1">
					<TabsTrigger
						v-for="tab in tabs"
						:key="tab.id"
						:value="tab.id"
						class="whitespace-nowrap min-w-fit px-3"
					>
						{{ tab.label }}
					</TabsTrigger>
				</TabsList>

				<TabsContent v-for="tab in tabs" :key="tab.id" :value="tab.id">
					<div v-if="groupedListings[tab.id]?.length" class="space-y-4">
						<RentalCard
							v-for="rental in groupedListings[tab.id]"
							:key="rental.id"
							:rental="rental"
							:cancellation-reasons="cancellationReasons"
							:user-role="userRole"
							:lender-schedules="rental.lender_schedules"
						/>
					</div>
					<div v-else class="text-muted-foreground py-10 text-center">
						No rentals found
					</div>
				</TabsContent>
			</Tabs>
		</div>

		<!-- Select dropdown for md screens -->
		<div class="lg:hidden">
			<Select v-model="selectedTab" @update:modelValue="handleValueChange">
				<SelectTrigger class="w-full">
					<SelectValue :placeholder="tabs.find((t) => t.id === selectedTab)?.label" />
				</SelectTrigger>
				<SelectContent>
					<SelectItem v-for="tab in tabs" :key="tab.id" :value="tab.id">
						{{ tab.label }}
					</SelectItem>
				</SelectContent>
			</Select>

			<!-- Mobile content -->
			<div class="mt-4">
				<div v-if="groupedListings[selectedTab]?.length" class="space-y-4">
					<RentalCard
						v-for="rental in groupedListings[selectedTab]"
						:key="rental.id"
						:rental="rental"
						:cancellation-reasons="cancellationReasons"
						:user-role="userRole"
						:lender-schedules="rental.lender_schedules"
					/>
				</div>
				<div v-else class="text-muted-foreground py-10 text-center">No rentals found</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.TabsList {
	overflow-x: auto;
	scrollbar-width: none; /* Firefox */
	-ms-overflow-style: none; /* IE and Edge */
}

.TabsList::-webkit-scrollbar {
	display: none; /* Chrome, Safari, Opera */
}
</style>
