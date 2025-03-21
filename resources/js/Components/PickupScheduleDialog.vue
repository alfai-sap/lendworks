<script setup>
import { Dialog, DialogContent } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import PickupDateSelector from "@/Components/PickupDateSelector.vue";
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";
import { format } from "date-fns"; // Add this import

// Add helper function for time formatting
const formatScheduleTime = (schedule) => {
	const formatTimeString = (timeStr) => {
		if (!timeStr) return "";
		const [hours, minutes] = timeStr.split(":");
		const date = new Date();
		date.setHours(parseInt(hours), parseInt(minutes));
		return date.toLocaleTimeString("en-US", {
			hour: "numeric",
			minute: "2-digit",
			hour12: true,
		});
	};

	return `${formatTimeString(schedule.start_time)} to ${formatTimeString(
		schedule.end_time
	)}`;
};

const props = defineProps({
	show: Boolean,
	rental: Object,
	userRole: String,
	lenderSchedules: Array,
});

const emits = defineEmits(["update:show", "schedule-confirmed"]);

const closeDialog = () => {
	emits("update:show", false);
};

const confirmForm = useForm({});

const selectedSchedule = computed(() =>
	props.rental.pickup_schedules?.find((s) => s.is_selected)
);

// Add computed property for suggested schedule
const isSuggestedSchedule = computed(() => {
	if (!selectedSchedule.value) return false;
	return selectedSchedule.value.is_suggested;
});

// Add new computed property
const shouldCloseAfterSelection = computed(() => {
	// Close immediately for renters suggesting a schedule
	if (props.userRole === "renter" && selectedSchedule.value?.is_suggested) {
		return true;
	}
	return false;
});

// Update shouldCloseAfterSelection computed to check role and selected schedule
const shouldShowLenderConfirmation = computed(() => {
	return (
		props.userRole === "lender" &&
		selectedSchedule.value &&
		!selectedSchedule.value.is_confirmed
	);
});

const handleConfirmSchedule = () => {
	confirmForm.patch(
		route("pickup-schedules.confirm", {
			rental: props.rental.id,
		}),
		{
			preserveScroll: true,
			preserveState: false, // Ensure fresh data load
			onSuccess: () => {
				emits("schedule-confirmed");
				emits("update:show", false);
			},
			onError: (errors) => {
				console.error("Schedule confirmation failed:", errors);
			},
		}
	);
};
</script>

<template>
	<Dialog :open="show" @update:open="closeDialog">
		<DialogContent class="max-w-2xl">
			<div class="space-y-4">
				<!-- Show lender confirmation view -->
				<div v-if="shouldShowLenderConfirmation" class="p-4">
					<div class="bg-muted p-4 rounded-lg space-y-4">
						<h3 class="font-medium text-base">Selected Pickup Schedule</h3>
						<div class="space-y-2">
							<p class="text-sm">
								<span class="text-muted-foreground">Date:</span>
								{{ format(new Date(selectedSchedule.pickup_datetime), "MMMM d, yyyy") }}
							</p>
							<p class="text-sm">
								<span class="text-muted-foreground">Time:</span>
								{{ formatScheduleTime(selectedSchedule) }}
							</p>
						</div>
					</div>

					<Button
						class="w-full mt-4"
						@click="handleConfirmSchedule"
						:disabled="confirmForm.processing"
					>
						{{ confirmForm.processing ? "Confirming..." : "Confirm Schedule" }}
					</Button>
				</div>

				<!-- Show date selector for renters or when no schedule is selected -->
				<div v-else>
					<PickupDateSelector
						:rental="rental"
						:userRole="userRole"
						:lenderSchedules="lenderSchedules"
						@schedule-selected="closeDialog"
					/>
				</div>
			</div>
		</DialogContent>
	</Dialog>
</template>
