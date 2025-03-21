<script setup>
import { computed, ref } from "vue";
import { formatNumber, formatRentalDate } from "@/lib/formatters";
import { Button } from "@/components/ui/button";
import BaseRentalCard from "@/Components/BaseRentalCard.vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import PaymentDialog from "@/Components/PaymentDialog.vue";
import HandoverDialog from "@/Components/HandoverDialog.vue";
import PickupScheduleDialog from "@/Components/PickupScheduleDialog.vue";
import { useForm } from "@inertiajs/vue3";
import { format } from "date-fns";

const props = defineProps({
	rental: {
		type: Object,
		required: true,
	},
	cancellationReasons: {
		type: Array,
		required: true,
	},
	userRole: {
		type: String,
		required: true,
	},
	lenderSchedules: {
		type: Array,
		default: () => [],
	},
});

const showCancelDialog = ref(false);
const showPaymentDialog = ref(false);
const showHandoverDialog = ref(false);
const showScheduleDialog = ref(false);

// Update the cancelForm to include reason
const cancelForm = useForm({
	cancellation_reason_id: "",
	custom_feedback: "",
});

const isOtherReason = computed(() => {
	return cancelForm.cancellation_reason_id === "other";
});

const handleCancel = () => {
	cancelForm.patch(route("rental-request.cancel", props.rental.id), {
		onSuccess: () => {
			showCancelDialog.value = false;
			// Reset form
			cancelForm.cancellation_reason_id = "";
			cancelForm.custom_feedback = "";
		},
	});
};

// computed property for image URL
const listingImage = computed(() => {
	const image = props.rental?.listing?.images?.[0];
	if (image?.image_path) {
		return `/storage/${image.image_path}`;
	}
	return "/storage/images/listing/default.png";
});

// Add overdue status check
const isOverdue = computed(() => {
	if (props.rental.status !== "active") return false;
	return new Date(props.rental.end_date) < new Date();
});

// Update isPaidOverdue computed to properly check for verified payment
const isPaidOverdue = computed(() => {
	return (
		isOverdue.value &&
		props.rental.payment_request?.type === "overdue" &&
		props.rental.payment_request?.status === "verified"
	);
});

// Add new computed for pending overdue payment
const hasPendingOverduePayment = computed(() => {
	return (
		isOverdue.value &&
		props.rental.payment_request?.type === "overdue" &&
		props.rental.payment_request?.status === "pending"
	);
});

// Add selected pickup schedule computed
const selectedPickupSchedule = computed(() =>
	props.rental.pickup_schedules?.find((s) => s.is_selected && s.is_confirmed)
);

// Update the details computed to include quantity and overdue information
const details = computed(() => {
	const baseDetails = [
		{
			label: "Total",
			value: formatNumber(props.rental.total_price),
		},
		{
			label: "Quantity",
			value: props.rental.quantity_approved
				? `${props.rental.quantity_approved} unit(s)`
				: `${props.rental.quantity_requested} requested`,
		},
		{
			label: "Period",
			value: `${formatRentalDate(props.rental.start_date)} - ${formatRentalDate(
				props.rental.end_date
			)}`,
		},
		{
			label: "Owner",
			value: props.rental.listing.user.name,
		},
	];

	// Only show meetup schedule if not yet active
	const schedule = props.rental.pickup_schedules?.find((s) => s.is_selected);
	if (schedule && props.rental.status !== "active") {
		const pickupDate = new Date(schedule.pickup_datetime);
		baseDetails.push({
			label: "Meetup",
			value: `${format(pickupDate, "MMMM d")}, ${format(pickupDate, "EEEE")}`,
		});
	}

	// For active rentals, add duration info instead of meetup
	if (props.rental.status === "active") {
		baseDetails.push(
			{
				label: "Duration",
				value: `${props.rental.rental_duration} days`,
				class: "text-primary",
			},
			{
				label: isOverdue.value ? "Overdue By" : "Remaining",
				value: isOverdue.value
					? `${props.rental.overdue_days} days`
					: `${props.rental.remaining_days} days`,
				class: isOverdue.value ? "text-red-600" : "text-muted-foreground",
			}
		);
	}

	// Add overdue days if rental is overdue
	if (isOverdue.value) {
		baseDetails.push({
			label: isPaidOverdue.value ? "Paid Overdue Days" : "Overdue Days",
			value: `${props.rental.overdue_days} days`,
			class: isPaidOverdue.value ? "text-amber-600" : "text-red-600",
		});
	}

	return baseDetails;
});

// computed property to check if rental has payment
const payment = computed(() => props.rental.payment_request);

// list of actions available for the rental as defined in the model
const actions = computed(() => props.rental.available_actions);
</script>

<template>
	<BaseRentalCard
		:title="rental.listing.title"
		:image="listingImage"
		:status="rental.status"
		:paymentRequest="rental.payment_request"
		:listing-id="rental.listing.id"
		:details="details"
		@click="$inertia.visit(route('rental.show', rental.id))"
	>
		<!-- Additional details slot -->
		<template #additional-details>
			<!-- Show overdue message without the tracker -->
			<div
				v-if="isOverdue"
				class="mt-4"
				:class="{
					'text-red-600': !isPaidOverdue && !hasPendingOverduePayment,
					'text-amber-600': hasPendingOverduePayment,
					'text-green-600': isPaidOverdue,
				}"
			>
				<p v-if="isPaidOverdue" class="text-sm font-medium">
					Overdue fees have been paid. You can now proceed with return process.
				</p>
				<p v-else-if="hasPendingOverduePayment" class="text-sm font-medium">
					Overdue payment submitted - awaiting verification.
				</p>
				<p v-else class="text-sm font-medium">
					Rental is overdue. Overdue fees are now being applied.
				</p>
			</div>
		</template>

		<!-- Actions slot -->
		<template #actions>
			<div class="sm:justify-end flex flex-wrap gap-2">
				<!-- Payment Actions -->
				<Button
					v-if="actions.canPayNow"
					variant="default"
					size="sm"
					@click.stop="showPaymentDialog = true"
				>
					Pay Now
				</Button>

				<!-- Handover Actions -->
				<Button
					v-if="actions.canReceive"
					variant="default"
					size="sm"
					@click.stop="showHandoverDialog = true"
				>
					Confirm Receipt
				</Button>

				<!-- Cancel Action -->
				<Button
					v-if="actions.canCancel"
					variant="destructive"
					size="sm"
					:disabled="cancelForm.processing"
					@click.stop="showCancelDialog = true"
				>
					Cancel Request
				</Button>

				<!-- Add schedule selection button -->
				<Button
					v-if="actions.canChoosePickupSchedule"
					variant="default"
					size="sm"
					@click.stop="showScheduleDialog = true"
				>
					Choose Pickup Schedule
				</Button>
			</div>
		</template>
	</BaseRentalCard>

	<!-- Payment Dialog -->
	<PaymentDialog
		v-model:show="showPaymentDialog"
		:rental="rental"
		:payment="payment"
		:viewOnly="false"
	/>

	<!-- Handover Dialog -->
	<HandoverDialog
		v-model:show="showHandoverDialog"
		:rental="rental"
		:type="actions.canHandover ? 'handover' : 'receive'"
	/>

	<!-- Add dialog -->
	<PickupScheduleDialog
		v-model:show="showScheduleDialog"
		:rental="rental"
		:userRole="userRole"
		:lenderSchedules="lenderSchedules"
	/>
</template>
