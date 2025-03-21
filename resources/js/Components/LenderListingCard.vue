<script setup>
import { computed, ref } from "vue";
import { formatNumber, formatRentalDate } from "@/lib/formatters";
import BaseRentalCard from "@/Components/BaseRentalCard.vue";
import { Button } from "@/components/ui/button";
import { useForm } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import HandoverDialog from "@/Components/HandoverDialog.vue";
import { format } from "date-fns";
import PickupScheduleDialog from "@/Components/PickupScheduleDialog.vue";

const props = defineProps({
	data: {
		type: Object,
		required: true,
	},
	selectedStatus: {
		type: String,
		required: true,
	},
	rejectionReasons: {
		type: Array,
		required: true,
	},
	cancellationReasons: {
		type: Array,
		required: true,
	},
	lenderSchedules: {
		type: Array,
		default: () => [],
	},
});

// Image handling
const listingImage = computed(() => {
	const image = props.data.listing?.images?.[0];
	return image?.image_path
		? `/storage/${image.image_path}`
		: "/storage/images/listing/default.png";
});

// Add overdue status check
const isOverdue = computed(() => {
	if (props.data.rental_request.status !== "active") return false;
	return new Date(props.data.rental_request.end_date) < new Date();
});

// Update isPaidOverdue computed to properly check for verified payment
const isPaidOverdue = computed(() => {
	return (
		isOverdue.value &&
		props.data.rental_request.payment_request?.type === "overdue" &&
		props.data.rental_request.payment_request?.status === "verified"
	);
});

// Add new computed for pending overdue payment
const hasPendingOverduePayment = computed(() => {
	return (
		isOverdue.value &&
		props.data.rental_request.payment_request?.type === "overdue" &&
		props.data.rental_request.payment_request?.status === "pending"
	);
});

// Update details computed to include overdue information
const details = computed(() => {
	const baseDetails = [
		{
			label: "Total",
			value: formatNumber(props.data.rental_request.total_price),
		},
		{
			label: "Quantity",
			value: props.data.rental_request.quantity_approved
				? `${props.data.rental_request.quantity_approved} approved (${props.data.rental_request.quantity_requested} requested)`
				: `${props.data.rental_request.quantity_requested} unit(s) requested`,
		},
		{
			label: "Period",
			value: `${formatRentalDate(
				props.data.rental_request.start_date
			)} - ${formatRentalDate(props.data.rental_request.end_date)}`,
		},
		{
			label: "Renter",
			value: props.data.rental_request.renter.name,
		},
	];

	// Only show meetup schedule if not yet active and schedule is confirmed
	const schedule = props.data.rental_request.pickup_schedules?.find(
		(s) => s.is_selected && s.is_confirmed
	);
	if (schedule && props.data.rental_request.status !== "active") {
		const pickupDate = new Date(schedule.pickup_datetime);
		baseDetails.push({
			label: "Meetup",
			value: `${format(pickupDate, "MMMM d")}, ${format(pickupDate, "EEEE")}`,
		});
	}

	// For active rentals, add duration info instead of meetup
	if (props.data.rental_request.status === "active") {
		baseDetails.push(
			{
				label: "Duration",
				value: `${props.data.rental_request.rental_duration} days`,
				class: "text-primary",
			},
			{
				label: isOverdue.value ? "Overdue By" : "Remaining",
				value: isOverdue.value
					? `${props.data.rental_request.overdue_days} days`
					: `${props.data.rental_request.remaining_days} days`,
				class: isOverdue.value ? "text-red-600" : "text-muted-foreground",
			}
		);
	}

	// Add overdue days if rental is overdue
	if (isOverdue.value) {
		baseDetails.push({
			label: isPaidOverdue.value ? "Paid Overdue Days" : "Overdue Days",
			value: `${props.data.rental_request.overdue_days} days`,
			class: isPaidOverdue.value ? "text-amber-600" : "text-red-600",
		});
	}

	return baseDetails;
});

const showRejectDialog = ref(false);
const showAcceptDialog = ref(false);
const showCancelDialog = ref(false);
const showHandoverDialog = ref(false);
const showPickupDialog = ref(false);
const showNoShowDialog = ref(false);
const approveForm = useForm({
	quantity_approved: 1,
});
const rejectForm = useForm({
	rejection_reason_id: "",
	custom_feedback: "",
});
const cancelForm = useForm({
	cancellation_reason_id: "",
	custom_feedback: "",
});

const isOtherReason = computed(() => {
	if (rejectForm.rejection_reason_id) {
		return rejectForm.rejection_reason_id === "other";
	}
	return cancelForm.cancellation_reason_id === "other";
});

// maxApproveQuantity computed property
const maxApproveQuantity = computed(() =>
	Math.min(
		props.data.rental_request.quantity_requested,
		props.data.listing.available_quantity
	)
);

const handleApprove = () => {
	approveForm.patch(route("rental-request.approve", props.data.rental_request.id), {
		preserveScroll: true,
		onSuccess: () => {
			showAcceptDialog.value = false;
			approveForm.quantity_approved = 1;
		},
	});
};

const handleReject = () => {
	rejectForm.patch(route("rental-request.reject", props.data.rental_request.id), {
		onSuccess: () => {
			showRejectDialog.value = false;
		},
		preserveScroll: true,
	});
};

const handleCancel = () => {
	cancelForm.patch(route("rental-request.cancel", props.data.rental_request.id), {
		onSuccess: () => {
			showCancelDialog.value = false;
			// Reset form
			cancelForm.cancellation_reason_id = "";
			cancelForm.custom_feedback = "";
		},
		preserveScroll: true,
	});
};

// Get available actions from the rental request
const actions = computed(() => props.data.rental_request.available_actions);

// computed property for payment request
const paymentRequest = computed(() => props.data.rental_request.payment_request);

const hasNoSchedule = computed(() => {
	// Return true if there are no schedules or no selected schedule
	return !props.data.rental_request.pickup_schedules?.some((s) => s.is_selected);
});

const hasUnconfirmedSchedule = computed(() => {
	// Return true if there is a selected but unconfirmed schedule
	const selectedSchedule = props.data.rental_request.pickup_schedules?.find(
		(s) => s.is_selected
	);
	return selectedSchedule && !selectedSchedule.is_confirmed;
});

// Add event handler for schedule confirmation
const handleScheduleConfirmed = () => {
	showPickupDialog.value = false;
};

const selectedPickupSchedule = computed(() =>
	props.data.rental_request.pickup_schedules?.find((s) => s.is_selected && s.is_confirmed)
);
</script>

<template>
	<BaseRentalCard
		:title="data.listing.title"
		:image="listingImage"
		:status="data.rental_request.status"
		:payment-request="paymentRequest"
		:listing-id="data.listing.id"
		:details="details"
		@click="$inertia.visit(route('rental.show', data.rental_request.id))"
	>
		<!-- Additional details slot -->
		<template #additional-details>
			<!-- Remove RentalDurationTracker and only show overdue message -->
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
					Overdue fees have been paid. Please proceed with return process.
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
				<!-- Schedule Related Actions -->
				<template v-if="hasUnconfirmedSchedule">
					<Button variant="default" size="sm" @click.stop="showPickupDialog = true">
						Confirm Schedule
					</Button>
				</template>

				<!-- Handover Action -->
				<template v-if="actions.canHandover">
					<Button variant="default" size="sm" @click.stop="showHandoverDialog = true">
						Hand Over Item
					</Button>
				</template>

				<!-- Waiting Message -->
				<Button
					v-if="actions.canHandover && hasNoSchedule"
					variant="default"
					size="sm"
					disabled
				>
					Waiting for Schedule
				</Button>

				<!-- Other actions remain unchanged -->
				<Button
					v-if="actions.canApprove"
					variant="default"
					size="sm"
					:disabled="approveForm.processing"
					@click.stop="showAcceptDialog = true"
				>
					{{ approveForm.processing ? "Approving..." : "Approve" }}
				</Button>

				<Button
					v-if="actions.canReject"
					variant="destructive"
					size="sm"
					:disabled="rejectForm.processing"
					@click.stop="showRejectDialog = true"
				>
					Reject
				</Button>

				<Button
					v-if="actions.canCancel"
					variant="destructive"
					size="sm"
					:disabled="cancelForm.processing"
					@click.stop="showCancelDialog = true"
				>
					Cancel Request
				</Button>

				<Button
					v-if="
						data.rental_request.can_report_no_show &&
						data.rental_request.status === 'to_handover' &&
						selectedPickupSchedule
					"
					variant="destructive"
					size="sm"
					@click.stop="showNoShowDialog = true"
				>
					Report No-Show
				</Button>
			</div>
		</template>
	</BaseRentalCard>

	<!-- Accept Dialog -->
	<ConfirmDialog
		:show="showAcceptDialog"
		title="Approve Rental Request"
		:description="`Renter requested ${data.rental_request.quantity_requested} unit(s). Please specify how many units you want to approve.`"
		confirmLabel="Approve Request"
		confirmVariant="default"
		:processing="approveForm.processing"
		:showQuantity="true"
		:quantityValue="approveForm.quantity_approved"
		:maxQuantity="maxApproveQuantity"
		@update:show="showAcceptDialog = $event"
		@update:quantityValue="approveForm.quantity_approved = $event"
		@confirm="handleApprove"
		@cancel="showAcceptDialog = false"
	/>

	<!-- Reject Dialog -->
	<ConfirmDialog
		:show="showRejectDialog"
		title="Reject Rental Request"
		description="Please select a reason for rejecting this rental request."
		confirmLabel="Reject Request"
		confirmVariant="destructive"
		:processing="rejectForm.processing"
		:disabled="
			!rejectForm.rejection_reason_id || (isOtherReason && !rejectForm.custom_feedback)
		"
		showSelect
		:selectOptions="rejectionReasons"
		:selectValue="rejectForm.rejection_reason_id"
		:showTextarea="isOtherReason"
		:textareaValue="rejectForm.custom_feedback"
		:textareaRequired="isOtherReason"
		textareaPlaceholder="Please provide specific details about why you are rejecting this rental request..."
		@update:show="showRejectDialog = $event"
		@update:selectValue="rejectForm.rejection_reason_id = $event"
		@update:textareaValue="rejectForm.custom_feedback = $event"
		@confirm="handleReject"
		@cancel="showAcceptDialog = false"
	/>

	<!-- Cancel Dialog -->
	<ConfirmDialog
		:show="showCancelDialog"
		title="Cancel Rental Request"
		description="Please select a reason for cancelling this rental request."
		confirmLabel="Cancel Request"
		confirmVariant="destructive"
		:processing="cancelForm.processing"
		:disabled="
			!cancelForm.cancellation_reason_id || (isOtherReason && !cancelForm.custom_feedback)
		"
		showSelect
		:selectOptions="cancellationReasons"
		:selectValue="cancelForm.cancellation_reason_id"
		:showTextarea="isOtherReason"
		:textareaValue="cancelForm.custom_feedback"
		:textareaRequired="isOtherReason"
		textareaPlaceholder="Please provide specific details about why you are cancelling this rental request..."
		@update:show="showCancelDialog = $event"
		@update:selectValue="cancelForm.cancellation_reason_id = $event"
		@update:textareaValue="cancelForm.custom_feedback = $event"
		@confirm="handleCancel"
		@cancel="showCancelDialog = false"
	/>

	<!-- Handover Dialog -->
	<HandoverDialog
		v-model:show="showHandoverDialog"
		:rental="data.rental_request"
		:lender-schedules="lenderSchedules"
		type="handover"
	/>

	<!-- Pickup Schedule Dialog -->
	<PickupScheduleDialog
		v-model:show="showPickupDialog"
		:rental="data.rental_request"
		:userRole="'lender'"
		:lenderSchedules="lenderSchedules"
		@schedule-confirmed="handleScheduleConfirmed"
	/>

	<NoShowDisputeDialog
		v-if="selectedPickupSchedule"
		v-model:show="showNoShowDialog"
		:rental="data.rental_request"
		:type="'renter_no_show'"
		:schedule="selectedPickupSchedule"
	/>
</template>
