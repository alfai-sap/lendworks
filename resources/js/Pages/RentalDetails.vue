<script setup>
import { Head } from "@inertiajs/vue3";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import RentalStatusBadge from "@/Components/RentalStatusBadge.vue";
import { formatNumber, formatDateTime, timeAgo } from "@/lib/formatters";
import { Separator } from "@/components/ui/separator";
import { Package, Clock } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { computed } from "vue";
import { calculateDiscountPercentage } from "@/lib/rentalCalculator";
import { useForm } from "@inertiajs/vue3";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import { ref } from "vue";
import RentalTimeline from "@/Components/RentalTimeline.vue";
import { Link } from "@inertiajs/vue3";
import PaymentDialog from "@/Components/PaymentDialog.vue";
import HandoverDialog from "@/Components/HandoverDialog.vue";
import RentalDurationTracker from "@/Components/RentalDurationTracker.vue";
import ReturnScheduler from "@/Components/ReturnScheduler.vue";
import ReturnConfirmationDialog from "@/Components/ReturnConfirmationDialog.vue";
import PaymentProofDialog from "@/Components/PaymentProofDialog.vue";
import DisputeDialog from "@/Components/DisputeDialog.vue";
import PickupScheduleDialog from "@/Components/PickupScheduleDialog.vue";
import { format } from "date-fns";
import ReturnScheduleDialog from "@/Components/ReturnScheduleDialog.vue";

const props = defineProps({
	rental: Object,
	userRole: String,
	rejectionReasons: Array,
	cancellationReasons: Array,
	lenderSchedules: Array,
});

// Computed properties for role-specific content
const roleSpecificName = computed(() => {
	if (props.userRole === "renter") {
		return {
			label: "Lender",
			name: props.rental.listing.user.name,
		};
	}
	return {
		label: "Renter",
		name: props.rental.renter.name,
	};
});

const rentalDays = computed(() => {
	const start = new Date(props.rental.start_date);
	const end = new Date(props.rental.end_date);

	// Set both dates to start of day in Manila timezone
	start.setHours(0, 0, 0, 0);
	end.setHours(0, 0, 0, 0);

	const diffTime = Math.abs(end.getTime() - start.getTime());

	// Add 1 to include both start and end dates
	return Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;
});

const discountPercentage = computed(() =>
	calculateDiscountPercentage(rentalDays.value, props.rental.listing.value)
);

// Dialog states
const showCancelDialog = ref(false);
const showRejectDialog = ref(false);
const showAcceptDialog = ref(false);
const showHandoverDialog = ref(false);
const showReturnDialog = ref(false);
const returnDialogType = ref("submit");
const showDisputeDialog = ref(false);
const showScheduleDialog = ref(false);
const showReturnScheduleDialog = ref(false);
const showPickupDialog = ref(false);

// Forms
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

const initiateForm = useForm({});
const confirmForm = useForm({}); // Add this line
const showEarlyReturnDialog = ref(false);

const handleInitiateReturn = () => {
	const today = new Date();
	const endDate = new Date(props.rental.end_date);

	today.setHours(0, 0, 0, 0);
	endDate.setHours(0, 0, 0, 0);

	if (today < endDate) {
		showEarlyReturnDialog.value = true;
	} else {
		proceedWithReturn();
	}
};

const proceedWithReturn = () => {
	initiateForm.post(route("rentals.initiate-return", props.rental.id), {
		preserveScroll: true,
		onSuccess: () => {
			showEarlyReturnDialog.value = false;
		},
	});
};

const isOtherReason = computed(() => {
	const rejectionReason = rejectionReasons.value.find(
		(r) => r.value === rejectForm.rejection_reason_id
	);
	const cancellationReason = cancellationReasons.value.find(
		(r) => r.value === cancelForm.cancellation_reason_id
	);

	return (
		rejectionReason?.label === "Other Reason" || cancellationReason?.label === "Other"
	);
});

// Reactive references for select options
const rejectionReasons = computed(() => props.rejectionReasons || []);
const cancellationReasons = computed(() => props.cancellationReasons || []);

// Handle actions
const handleApprove = () => {
	approveForm.patch(route("rental-request.approve", props.rental.id), {
		preserveScroll: true,
		onSuccess: () => {
			showAcceptDialog.value = false;
			approveForm.quantity_approved = 1;
		},
	});
};

const handleReject = () => {
	rejectForm.patch(route("rental-request.reject", props.rental.id), {
		preserveScroll: true,
		onSuccess: () => {
			showRejectDialog.value = false;
			// Reset form
			rejectForm.rejection_reason_id = "";
			rejectForm.custom_feedback = "";
		},
	});
};

const handleCancel = () => {
	cancelForm.patch(route("rental-request.cancel", props.rental.id), {
		preserveScroll: true,
		onSuccess: () => {
			showCancelDialog.value = false;
			// Reset form
			cancelForm.cancellation_reason_id = "";
			cancelForm.custom_feedback = "";
		},
	});
};

// Add computed property for payment
const payment = computed(() => props.rental.payment_request);

// Add ref for payment dialog
const showPaymentDialog = ref(false);

// list of actions available for the rental as defined in the model
const actions = computed(() => props.rental.available_actions);

const lenderPayment = computed(() =>
	props.rental.completion_payments?.find((p) => p.type === "lender_payment")
);

const depositRefund = computed(() =>
	props.rental.completion_payments?.find((p) => p.type === "deposit_refund")
);

// Add new refs for payment proof dialog
const showPaymentProofDialog = ref(false);
const selectedPayment = ref(null);

// Add showPaymentProof function
const showPaymentProof = (payment) => {
	selectedPayment.value = payment;
	showPaymentProofDialog.value = true;
};

// Add these new computed properties after displayTotal
const rentalOnlyTotal = computed(() => {
	const base = props.rental.base_price - props.rental.discount;
	return props.userRole === "renter"
		? base + props.rental.service_fee
		: base - props.rental.service_fee;
});

const totalWithDeposit = computed(() => {
	return rentalOnlyTotal.value + props.rental.deposit_fee;
});

// Add a computed property for showing overdue sections
const showOverdueSection = computed(() => {
  // Only show if there are actual overdue days or an overdue payment
  return (
    props.rental.overdue_days > 0 ||
    props.rental.is_overdue ||
    props.rental.overdue_payment ||
    props.rental.payment_request?.type === "overdue"
  );
});

// Add computed for max allowed quantity
const maxApproveQuantity = computed(() =>
	Math.min(props.rental.quantity_requested, props.rental.listing.available_quantity)
);

// Add computed for pickup schedule
const pickupSchedule = computed(() => {
	return props.rental.pickup_schedules?.find((s) => s.is_selected);
});

const pickupScheduleDetails = computed(() => {
	if (!pickupSchedule.value) return null;

	const pickupDate = new Date(pickupSchedule.value.pickup_datetime);
	return {
		dayOfWeek: format(pickupDate, "EEEE"),
		date: format(pickupDate, "MMMM d, yyyy"),
		timeFrame: `${formatTime(pickupSchedule.value.start_time)} to ${formatTime(
			pickupSchedule.value.end_time
		)}`,
	};
});

const formatTime = (timeStr) => {
	const [hours, minutes] = timeStr.split(":");
	const date = new Date();
	date.setHours(parseInt(hours), parseInt(minutes));
	return date.toLocaleTimeString("en-US", {
		hour: "numeric",
		minute: "2-digit",
		hour12: true,
	});
};

// Add this computed property
const showReturnScheduleButton = computed(
	() =>
		props.rental.status === "pending_return" &&
		props.userRole === "renter" &&
		!props.rental.return_schedules?.some((s) => s.is_selected)
);

// Add these computed properties
const hasNoSchedule = computed(() => {
	return !props.rental.pickup_schedules?.some((s) => s.is_selected);
});

const hasUnconfirmedSchedule = computed(() => {
	const selectedSchedule = props.rental.pickup_schedules?.find((s) => s.is_selected);
	return selectedSchedule && !selectedSchedule.is_confirmed;
});

const canShowHandover = computed(() => {
	return actions.value.canHandover;
});

// Add computed for pickup schedule
const selectedPickupSchedule = computed(() =>
	props.rental.pickup_schedules?.find((s) => s.is_selected && s.is_confirmed)
);

// Add new computed property for confirm schedule button visibility
const showConfirmReturnScheduleButton = computed(() => {
  const hasUnconfirmedSchedule = props.rental.return_schedules?.some(s => 
    s.is_selected && !s.is_confirmed
  );
  return props.userRole === 'lender' && hasUnconfirmedSchedule;
});

// Add the handleConfirmSchedule function
const handleConfirmSchedule = () => {
  confirmForm.patch(route('return-schedules.confirm', {
    rental: props.rental.id
  }), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Return schedule confirmed successfully');
    }
  });
};
</script>

<template>
	<Head title="Rental Details" />

	<div class="space-y-8">
		<!-- Header -->
		<div
			class="sm:flex-row sm:items-center sm:justify-between bg-card flex flex-col gap-4 p-6 border rounded-lg shadow-sm"
		>
			<div class="space-y-2">
				<div class="flex items-center gap-2">
					<Package class="text-muted-foreground w-5 h-5" />
					<h2 class="text-2xl font-semibold tracking-tight">
						Transaction #{{ rental.id }}
					</h2>
				</div>
				<p class="text-muted-foreground text-sm">
					Created {{ timeAgo(rental.created_at) }}
				</p>
			</div>
			<RentalStatusBadge
				:status="rental.status"
				:paymentRequest="rental.payment_request"
				class="sm:text-base self-start text-sm"
			/>
		</div>

		<RentalDurationTracker :rental="rental" />

		<Card class="shadow-sm overflow-hidden">
			<CardHeader class="bg-card border-b">
				<CardTitle>Timeline</CardTitle>
			</CardHeader>
			<CardContent class="p-3 sm:p-6">
				<RentalTimeline
					:events="rental.timeline_events"
					:userRole="userRole"
					:rental="rental"
				/>
			</CardContent>
		</Card>

		<!-- Main Content -->
		<div class="grid gap-6 md:gap-8 md:grid-cols-[2fr_1fr]">
			<!-- Left Column -->
			<div class="space-y-8">
				<!-- Listing Details -->
				<Card class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle>Listing Details</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-6">
							<!-- Item Image and Basic Info -->
							<div class="sm:flex-row flex flex-col gap-4">
								<Link
									:href="route('listing.show', rental.listing.id)"
									class="sm:w-32 sm:h-32 flex-shrink-0 w-full h-48"
								>
									<img
										:src="
											rental.listing.images[0]?.image_path
												? `/storage/${rental.listing.images[0].image_path}`
												: '/storage/images/listing/default.png'
										"
										class="hover:opacity-90 object-cover w-full h-full transition-opacity rounded-lg"
										:alt="rental.listing.title"
									/>
								</Link>
								<div class="space-y-2">
									<Link
										:href="route('listing.show', rental.listing.id)"
										class="hover:text-primary transition-colors"
									>
										<h3 class="text-lg font-semibold">{{ rental.listing.title }}</h3>
									</Link>
									<p class="text-muted-foreground text-sm">
										Category: {{ rental.listing.category.name }}
									</p>
									<p class="text-muted-foreground text-sm">
										Meetup Location: {{ rental.listing.location.address }}
									</p>
								</div>
							</div>

							<Separator />

							<!-- Rental Period -->
							<div class="space-y-4">
								<h4 class="font-medium">Rental Period</h4>
								<div class="grid gap-4">
									<div class="sm:grid-cols-2 grid gap-4">
										<div>
											<p class="text-muted-foreground text-sm">Start Date</p>
											<p class="font-medium">
												{{ formatDateTime(rental.start_date, "MMMM D, YYYY") }}
											</p>
										</div>
										<div>
											<p class="text-muted-foreground text-sm">End Date</p>
											<p class="font-medium">
												{{ formatDateTime(rental.end_date, "MMMM D, YYYY") }}
											</p>
										</div>
									</div>
								</div>
							</div>

							<!-- Price Breakdown -->
							<div class="space-y-6">
								<h4 class="font-medium">Price Details</h4>
								<div class="space-y-4">
									<!-- Add quantity info message when approved quantity differs -->
									<div
										v-if="
											rental.quantity_approved &&
											rental.quantity_approved < rental.quantity_requested
										"
										class="text-amber-600 text-sm bg-amber-50 p-3 rounded-md"
									>
										Note: {{ rental.quantity_approved }} out of
										{{ rental.quantity_requested }} requested units were approved. The
										prices below reflect the approved quantity.
									</div>

									<!-- Base price -->
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">
											{{ formatNumber(rental.listing.price) }} × {{ rentalDays }} days
											<span v-if="rental.quantity_approved">
												× {{ rental.quantity_approved }} unit(s)
											</span>
											<span v-else> × {{ rental.quantity_requested }} unit(s) </span>
										</span>
										<span>{{ formatNumber(rental.base_price) }}</span>
									</div>

									<!-- Duration Discount -->
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">
											Duration Discount ({{ discountPercentage }}%)
										</span>
										<span class="text-emerald-500 font-medium">
											-{{ formatNumber(rental.discount) }}
										</span>
									</div>

									<!-- LendWorks Fee -->
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">LendWorks Fee</span>
										<span
											:class="{
												'text-emerald-500 font-medium': userRole === 'renter',
												'text-red-500 font-medium': userRole === 'lender',
											}"
										>
											{{ userRole === "lender" ? "-" : "" }}
											{{ formatNumber(rental.service_fee) }}
										</span>
									</div>

									<Separator class="my-4" />

									<!-- Rental Only Total -->
									<div class="flex justify-between pb-2 font-medium">
										<span>{{
											userRole === "renter" ? "Rental Amount" : "Total Earnings"
										}}</span>
										<span class="text-primary">{{ formatNumber(rentalOnlyTotal) }}</span>
									</div>

									<!-- Security Deposit section -->
									<template v-if="userRole === 'renter'">
										<div class="pt-4 mt-6 space-y-4 border-t">
											<div class="flex justify-between text-sm">
												<span class="text-muted-foreground"
													>Security Deposit (Refundable)</span
												>
												<span class="text-primary">{{
													formatNumber(rental.deposit_fee)
												}}</span>
											</div>

											<div class="flex justify-between mt-2 font-medium">
												<span>Total Payment Required</span>
												<span class="text-primary">{{
													formatNumber(totalWithDeposit)
												}}</span>
											</div>
										</div>
									</template>
									<template v-else>
										<p class="text-muted-foreground mt-4 text-xs">
											- Security deposit ({{ formatNumber(rental.deposit_fee) }}) is not
											included in your earnings and will be refunded to the renter.
										</p>
									</template>

									<!-- Overdue section -->
									<template v-if="rental.overdue_days > 0">
										<div
											class="text-destructive flex justify-between text-sm font-medium"
										>
											<span>Overdue Fee</span>
											<span>+ {{ formatNumber(rental.overdue_fee) }}</span>
										</div>
										<p class="text-muted-foreground text-xs">
											See Overdue Status Details section for complete breakdown
										</p>
									</template>

									<p class="text-muted-foreground mt-6 text-xs">
										- Security deposit will be refunded after the rental period, subject
										to item condition
									</p>
								</div>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- After the Listing Details Card and before the Pickup schedule input -->
				<Card v-if="showOverdueSection" class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle class="flex items-center gap-2">
							<Clock class="text-destructive w-4 h-4" />
							Overdue Status Details
						</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-6">
							<!-- Overdue Period Info -->
							<div class="space-y-4">
								<div class="sm:grid-cols-2 grid gap-4">
									<div>
										<p class="text-muted-foreground text-sm">Original End Date</p>
										<p class="font-medium">
											{{ formatDateTime(rental.end_date, "MMMM D, YYYY") }}
										</p>
									</div>
									<div>
										<p class="text-muted-foreground text-sm">
											{{ rental.overdue_payment ? 'Status' : 'Days Overdue' }}
										</p>
										<p v-if="rental.overdue_payment" class="text-emerald-500 font-medium">
											Overdue Paid
										</p>
										<p v-else class="text-destructive font-medium">
											{{ rental.overdue_days }} days
										</p>
									</div>
								</div>
							</div>

							<Separator />

							<!-- Enhanced Fee Breakdown -->
							<div class="space-y-4">
								<h4 class="font-medium">Fee Breakdown</h4>
								<div class="space-y-4">
									<!-- Daily Rate and Quantity -->
									<div class="flex justify-between text-sm">
										<div class="space-y-1">
											<span class="text-muted-foreground">Daily Rate</span>
											<p class="text-xs text-muted-foreground">
												{{ rental.quantity_approved || rental.quantity_requested }}
												unit(s) × {{ formatNumber(rental.listing.price) }}/day
											</p>
										</div>
										<span>{{
											formatNumber(
												rental.listing.price *
													(rental.quantity_approved || rental.quantity_requested)
											)
										}}</span>
									</div>

									<!-- Overdue Days -->
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">Days Overdue</span>
										<span>× {{ rental.overdue_days }}</span>
									</div>

									<Separator />

									<!-- Total Fee -->
									<div class="flex justify-between font-medium">
										<div class="space-y-1">
											<span>Total Overdue Fee</span>
											<p class="text-xs text-muted-foreground">
												Fee per day × Days overdue
											</p>
										</div>
										<span class="text-destructive">{{
											formatNumber(rental.overdue_fee)
										}}</span>
									</div>
								</div>
							</div>

							<!-- Payment Status -->
							<div v-if="rental.overdue_payment" class="bg-muted p-4 mt-4 rounded-lg">
								<h4 class="mb-3 text-sm font-medium">Payment Status</h4>
								<div class="space-y-2">
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">Status</span>
										<span class="text-emerald-500">Verified</span>
									</div>
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">Verified On</span>
										<span>{{ formatDateTime(rental.overdue_payment.verified_at) }}</span>
									</div>
									<div class="flex justify-between text-sm">
										<span class="text-muted-foreground">Reference</span>
										<span>{{ rental.overdue_payment.reference_number }}</span>
									</div>
								</div>
							</div>

							<!-- Warning for unpaid overdue -->
							<div
								v-else-if="!rental.overdue_payment"
								class="bg-destructive/10 p-4 mt-4 rounded-lg"
							>
								<p class="text-destructive text-sm">
									 ⚠️ Overdue payment must be settled before proceeding with the return
									process
								</p>
								<p class="text-muted-foreground mt-2 text-xs">
									The total overdue fee is calculated based on your daily rental rate
									multiplied by the number of overdue days.
								</p>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Add ReturnScheduler after PickupScheduler -->
				<ReturnScheduler
					v-if="
						rental.status === 'active' ||
						rental.status === 'pending_return' ||
						rental.status === 'return_scheduled' ||
						rental.status === 'pending_return_confirmation'
					"
					:rental="rental"
					:userRole="userRole"
					:lenderSchedules="lenderSchedules"
				/>

				<!-- Add pickup schedule section after rental details -->
				<Card
					v-if="pickupSchedule?.is_confirmed && !rental.handover_at"
					class="shadow-sm"
				>
					<CardHeader class="bg-card border-b">
						<CardTitle class="text-lg">Pickup Details</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-6">
							<!-- Meetup Location -->
							<div class="space-y-2">
								<h4 class="font-medium">Meetup Location</h4>
								<div class="p-4 border rounded-lg bg-muted/30">
									<div class="space-y-2">
										<p class="font-medium">{{ rental.listing.location.address }}</p>
										<p class="text-muted-foreground text-sm">
											{{ rental.listing.location.city }},
											{{ rental.listing.location.province }}
											{{ rental.listing.location.postal_code }}
										</p>
									</div>
								</div>
							</div>

							<!-- Schedule Information -->
							<div v-if="pickupSchedule" class="space-y-2">
								<h4 class="font-medium">Scheduled Time</h4>
								<div class="p-4 border rounded-lg bg-muted/30">
									<div class="space-y-3">
										<div class="flex items-baseline justify-between">
											<span class="font-medium">{{
												pickupScheduleDetails.dayOfWeek
											}}</span>
											<span class="text-sm text-muted-foreground">{{
												pickupScheduleDetails.date
											}}</span>
										</div>
										<div class="flex items-center justify-between">
											<span class="text-sm text-muted-foreground">Time Frame</span>
											<span class="font-medium">{{
												pickupScheduleDetails.timeFrame
											}}</span>
										</div>
									</div>
								</div>
							</div>

							<!-- Important Notes -->
							<div class="space-y-2">
								<h4 class="font-medium">Important Notes</h4>
								<ul class="space-y-2 text-sm text-muted-foreground">
									<li class="flex items-center gap-2">
										<span class="text-primary">•</span>
										<span>Please arrive at the meetup location on time</span>
									</li>
									<li class="flex items-center gap-2">
										<span class="text-primary">•</span>
										<span
											>Verify the item's condition before completing the handover</span
										>
									</li>
								</ul>
							</div>

							<!-- Schedule Selection Button -->
							<div v-if="actions.canChoosePickupSchedule && !pickupSchedule">
								<Button class="w-full" @click="showScheduleDialog = true">
									Choose Pickup Schedule
								</Button>
							</div>
						</div>
					</CardContent>
				</Card>
			</div>

			<!-- Right Column -->
			<div class="space-y-8">
				<!-- Add this inside the main content area, before the Actions Card -->
				<Card
					v-if="
						userRole === 'renter' &&
						(rental.status === 'pending_return_confirmation' ||
							rental.status === 'pending_final_confirmation' ||
							rental.status === 'disputed')
					"
					class="shadow-sm"
				>
					<CardHeader class="bg-card border-b">
						<CardTitle>Status Update</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-4">
							<!-- Different messages based on status -->
							<template v-if="rental.status === 'pending_return_confirmation'">
								<p class="text-sm">
									Waiting for the lender to confirm receipt of the item. You will be
									notified once they review the return proof.
								</p>
								<div
									v-if="rental.return_proofs?.length > 0"
									class="bg-muted flex items-center gap-2 p-4 rounded-lg"
								>
									<Clock class="text-muted-foreground w-4 h-4" />
									<p class="text-muted-foreground text-sm">
										Return proof submitted on
										{{ formatDateTime(rental.return_proofs[0].created_at) }}
									</p>
								</div>
							</template>

							<template v-else-if="rental.status === 'pending_final_confirmation'">
								<p class="text-sm">
									The lender is performing final checks on the item. You will be notified
									once they complete the transaction.
								</p>
								<div class="bg-muted flex items-center gap-2 p-4 rounded-lg">
									<Clock class="text-muted-foreground w-4 h-4" />
									<p class="text-muted-foreground text-sm">
										Return confirmed on {{ formatDateTime(rental.return_at) }}
									</p>
								</div>
							</template>

							<template v-else-if="rental.status === 'disputed'">
								<div class="space-y-4">
									<p class="text-destructive text-sm font-medium">
										 ⚠️ The lender has raised a dispute regarding the returned item
									</p>
									<div class="bg-muted p-4 space-y-2 rounded-lg">
										<p class="text-sm font-medium">Dispute Reason:</p>
										<p class="text-muted-foreground text-sm">
											{{ rental.dispute.reason }}
										</p>
										<p class="text-muted-foreground mt-2 text-sm">
											The admin team will review this case and notify you of the outcome.
										</p>
									</div>
								</div>
							</template>

							<!-- Estimated processing time -->
							<div class="pt-4 mt-4 border-t">
								<p class="text-muted-foreground text-xs">
									Estimated processing time: 1-2 business days
								</p>
							</div>
						</div>
					</CardContent>
				</Card>

				<!-- Actions Card -->
				<Card class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle>Actions</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-4">
							 <!-- Add the Confirm Return Schedule button -->
							 <Button
								v-if="showConfirmReturnScheduleButton"
								variant="default"
								class="w-full"
								@click="handleConfirmSchedule"
								:disabled="confirmForm.processing"
							>
								Confirm Return Schedule
							</Button>

							<!-- Payment Actions -->
							<Button
								v-if="actions.canPayNow"
								variant="default"
								class="w-full"
								@click="showPaymentDialog = true"
							>
								Pay Now
							</Button>

							<!-- Add this before other action buttons -->
							<Button
								v-if="actions.canInitiateReturn"
								variant="default"
								class="w-full"
								@click="handleInitiateReturn"
								:disabled="initiateForm.processing"
							>
								Initiate Return Process
							</Button>

							<!-- Handover Actions -->
							<template
								v-if="(actions.canHandover || hasUnconfirmedSchedule || hasNoSchedule) && 
									rental.status !== 'active' && 
									rental.status !== 'pending_final_confirmation' && 
									rental.status !== 'disputed' &&
									rental.status !== 'completed_pending_payments' &&
									rental.status !== 'completed_with_payments'"
							>
								<!-- Show waiting message when no schedule -->
								<Button
									v-if="hasNoSchedule && userRole == 'lender' && canHandover"
									variant="default"
									class="w-full"
									disabled
								>
									Waiting for Schedule
								</Button>

								<!-- Show confirm button for unconfirmed schedule -->
								<Button
									v-else-if="hasUnconfirmedSchedule && userRole == 'lender'"
									variant="default"
									class="w-full"
									@click="showPickupDialog = true"
								>
									Confirm Schedule
								</Button>

								<!-- Show handover button only when schedule confirmed -->
								<Button
									v-else-if="canShowHandover"
									variant="default"
									class="w-full"
									@click="showHandoverDialog = true"
								>
									Hand Over Item
								</Button>
							</template>

							 <!-- Add Dispute Button -->
							 <Button
								v-if="rental.status === 'pending_final_confirmation' && 
									userRole === 'lender' && 
									(!rental.dispute || rental.dispute?.resolution_type === 'rejected')"
								variant="destructive"
								class="w-full"
								@click="showDisputeDialog = true"
							>
								Raise Dispute
							</Button>

							<!-- Rest of the actions remain unchanged -->
							<Button
								v-if="actions.canReceive"
								variant="default"
								class="w-full"
								@click="showHandoverDialog = true"
							>
								Confirm Receipt
							</Button>

							<!-- Add schedule selection button -->
							<Button
								v-if="actions.canChoosePickupSchedule"
								variant="default"
								class="w-full"
								@click="showScheduleDialog = true"
							>
								Choose Pickup Schedule
							</Button>

							<!-- Lender Actions -->
							<template v-if="actions.canApprove">
								<Button class="w-full" @click="showAcceptDialog = true">
									Approve Request
								</Button>
								<Button
									variant="destructive"
									class="w-full"
									@click="showRejectDialog = true"
								>
									Reject Request
								</Button>
							</template>

							<!-- Return Actions -->
							<Button
								v-if="actions.canSubmitReturn"
								variant="default"
								class="w-full"
								@click="
									() => {
										returnDialogType = 'submit';
										showReturnDialog = true;
									}
								"
							>
								Submit Return Proof
							</Button>

							<Button
								v-if="actions.canConfirmReturn"
								variant="default"
								class="w-full"
								@click="
									() => {
										returnDialogType = 'confirm';
										showReturnDialog = true;
									}
								"
							>
								Confirm Item Receipt
							</Button>

							<Button
								v-if="actions.canFinalizeReturn"
								variant="default"
								class="w-full"
								@click="
									() => {
										returnDialogType = 'finalize';
										showReturnDialog = true;
									}
								"
							>
								Complete Transaction
							</Button>

							<!-- Replace the existing return schedule button with this -->
							<Button
								v-if="showReturnScheduleButton"
								variant="default"
								class="w-full"
								@click="showReturnScheduleDialog = true"
							>
								Select Return Schedule
							</Button>

							<!-- Cancel Action -->
							<Button
								v-if="actions.canCancel"
								variant="destructive"
								class="w-full"
								:disabled="cancelForm.processing"
								@click="showCancelDialog = true"
							>
								Cancel Request
							</Button>

							<!-- No Actions Message -->
							<p
								v-if="
									!actions.canPayNow &&
									!actions.canCancel &&
									!actions.canApprove &&
									!actions.canHandover &&
									!hasUnconfirmedSchedule &&
									!hasNoSchedule &&
									!actions.canReceive &&
									!actions.canSubmitReturn &&
									!actions.canConfirmReturn &&
									!actions.canFinalizeReturn &&
									!actions.canChoosePickupSchedule &&
									!actions.canRaiseDispute &&
									!showReturnScheduleButton &&
									!actions.canInitiateReturn &&
									!showConfirmReturnScheduleButton
								"
								class="text-muted-foreground text-sm text-center"
							>
								No actions available at this time.
							</p>
						</div>
					</CardContent>
				</Card>

				<!-- Contact Information -->
				<Card class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle>{{ roleSpecificName.label }} Information</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-4">
							<div>
								<p class="text-muted-foreground text-sm">Name</p>
								<p class="font-medium">{{ roleSpecificName.name }}</p>
							</div>
							<div>
								<p class="text-muted-foreground text-sm">Member Since</p>
								<p class="font-medium">
									{{
										formatDateTime(
											userRole === "renter"
												? rental.listing.user.created_at
												: rental.renter.created_at,
											"MMMM D, YYYY"
										)
									}}
								</p>
							</div>
							<div>
								<p class="text-muted-foreground text-sm">Total Listings</p>
								<p class="font-medium">
									{{
										userRole === "renter"
											? rental.listing.user.listings_count
											: rental.renter.listings_count
									}}
									{{
										userRole === "renter"
											? "items listed"
											: rental.renter.listings_count === 1
											? "item listed"
											: "items listed"
									}}
								</p>
							</div>
							<div class="flex items-center gap-2">
								<Button variant="outline" size="sm" asChild>
									<Link :href="route('listing.show', rental.listing.id)"
										>View Profile</Link
									>
								</Button>
							</div>
						</div>
					</CardContent>
				</Card>

				<Card
					v-if="props.rental.status === 'completed_pending_payments' || props.rental.status === 'completed_with_payments'"
					class="shadow-sm"
				>
					<CardHeader class="bg-card border-b">
						<CardTitle>Payment Status</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div v-if="userRole === 'lender'" class="space-y-4">
							<template v-if="lenderPayment">
								<div class="bg-muted p-4 rounded-lg">
									<div class="flex items-center justify-between">
										<p class="text-sm font-medium">Payment Processed Successfully</p>
										<span class="text-emerald-500 text-xs font-medium">✓ Completed</span>
									</div>
									<p class="text-muted-foreground mt-2 text-sm">
										Reference: {{ lenderPayment.reference_number }}
									</p>
									<Button
										variant="outline"
										size="sm"
										class="mt-3"
										@click="showPaymentProof(lenderPayment)"
									>
										View Payment Proof
									</Button>
								</div>
							</template>
							<template v-else>
								<h3 class="font-medium">Payment Processing</h3>
								<p class="text-muted-foreground text-sm">
									Your payment is being processed by the admin. You will be notified once
									the payment has been sent.
								</p>
							</template>
						</div>

						<div v-if="userRole === 'renter'" class="space-y-4">
							<template v-if="depositRefund">
								<div class="bg-muted p-4 rounded-lg">
									<div class="flex items-center justify-between">
										<p class="text-sm font-medium">Security Deposit Refunded</p>
										<span class="text-emerald-500 text-xs font-medium">✓ Completed</span>
									</div>
									<p class="text-muted-foreground mt-2 text-sm">
										Reference: {{ depositRefund.reference_number }}
									</p>
									<Button
										variant="outline"
										size="sm"
										class="mt-3"
										@click="showPaymentProof(depositRefund)"
									>
										View Refund Proof
									</Button>
								</div>
							</template>
							<template v-else>
								<h3 class="font-medium">Deposit Refund Status</h3>
								<p class="text-muted-foreground text-sm">
									Your security deposit refund is being processed. You will be notified once
									it has been sent.
								</p>
							</template>
						</div>
					</CardContent>
				</Card>

				<!-- Update the lender earnings card -->
				<Card v-if="userRole === 'lender'" class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle class="flex items-center gap-2"> Lender Earnings </CardTitle>
					</CardHeader>
					<CardContent class="p-4">
						<div class="space-y-3">
							<div class="flex items-center justify-between">
								<span class="font-medium">Base Rental:</span>
								<span>{{ formatNumber(rental.base_price) }}</span>
							</div>
							<div class="text-destructive flex items-center justify-between">
								<span class="font-medium">Discounts & Fees:</span>
								<span>- {{ formatNumber(rental.discount + rental.service_fee) }}</span>
							</div>
							<div
								v-if="showOverdueSection"
								class="text-emerald-500 flex items-center justify-between"
							>
								<span class="font-medium">Overdue Fee:</span>
								<span>+ {{ formatNumber(rental.overdue_fee) }}</span>
							</div>
							<Separator />
							<div class="flex items-center justify-between">
								<span class="font-medium">Total Earnings:</span>
								<span class="text-emerald-500 text-lg">
									{{
										formatNumber(
											rental.base_price -
												rental.discount -
												rental.service_fee +
												(showOverdueSection ? rental.overdue_fee : 0)
										)
									}}
								</span>
							</div>
							<p class="text-muted-foreground text-xs">
								{{
									showOverdueSection
										? "Total earnings including overdue fees"
										: "Total earnings after discounts and fees"
								}}
							</p>
						</div>
					</CardContent>
				</Card>

				<!-- Add this before the end of the main grid div -->
				<Card v-if="rental.dispute" class="shadow-sm">
					<CardHeader class="bg-card border-b">
						<CardTitle class="text-destructive">Return Dispute</CardTitle>
					</CardHeader>
					<CardContent class="p-6">
						<div class="space-y-4">
							 <!-- Update message for multiple disputes -->
							 <div v-if="rental.disputes?.length > 1" class="text-sm text-muted-foreground mb-4">
								<p>Previous disputes: {{ rental.disputes.length - 1 }}</p>
							</div>

							<!-- Rejection Messages -->
							<div
								v-if="rental.dispute.resolution_type === 'rejected'"
								class="bg-destructive/10 border-destructive/20 p-4 mb-4 border rounded-lg"
							>
								<!-- Lender specific message -->
								<template v-if="userRole === 'lender'">
									<p class="text-destructive text-sm font-medium">
										 ⚠️ Your dispute claim has been rejected by the admin
									</p>
									<div class="mt-3 space-y-2">
										<p class="text-sm font-medium">Reason for Rejection:</p>
										<p class="text-sm">{{ rental.dispute.verdict }}</p>
										<p class="text-muted-foreground text-sm">
											{{ rental.dispute.verdict_notes }}
										</p>
									</div>
									<p class="mt-4 text-sm">
										You may raise a new dispute with additional evidence to support your
										claim.
									</p>
								</template>

								<!-- Renter specific message -->
								<template v-else>
									<p class="text-destructive text-sm font-medium">
										The dispute raised by the lender has been rejected
									</p>
									<div class="mt-3 space-y-2">
										<p class="text-sm font-medium">Admin's Decision:</p>
										<p class="text-sm">{{ rental.dispute.verdict }}</p>
										<p class="text-muted-foreground text-sm">
											{{ rental.dispute.verdict_notes }}
										</p>
									</div>
									<p class="mt-4 text-sm">The rental can now proceed to completion.</p>
								</template>
							</div>

							<!-- Only show dispute details if not rejected or if dispute is resolved -->
							<template v-if="rental.dispute.resolution_type !== 'rejected'">
								<div class="space-y-4">
									<!-- Resolved with Deduction Message -->
									<div
										v-if="rental.dispute.resolution_type === 'deposit_deducted'"
										class="bg-primary/10 border-primary/20 p-4 mb-4 border rounded-lg"
									>
										<div class="space-y-3">
											<p class="text-primary text-sm font-medium">
												 ✓ This dispute has been resolved with deposit deduction
											</p>

											<!-- Show amount details -->
											<div class="space-y-2">
												<div class="flex justify-between text-sm">
													<span class="text-muted-foreground">Deduction Amount:</span>
													<span class="font-medium">{{
														formatNumber(rental.dispute.deposit_deduction)
													}}</span>
												</div>
												<Separator />
												<p class="text-sm font-medium">Reason for Deduction:</p>
												<p class="text-muted-foreground text-sm">
													{{ rental.dispute.deposit_deduction_reason }}
												</p>
											</div>

											<!-- Different messages for lender and renter -->
											<div class="mt-2">
												<p
													v-if="userRole === 'lender'"
													class="text-muted-foreground text-sm"
												>
													The deducted amount has been added to your earnings.
												</p>
												<p v-else class="text-muted-foreground text-sm">
													This amount has been deducted from your security deposit.
												</p>
											</div>
										</div>
									</div>

									<!-- Original dispute details -->
									<div class="space-y-2">
										<h4 class="font-medium">Original Dispute Details</h4>
										<div class="space-y-2">
											<p class="text-sm font-medium">Reason:</p>
											<p class="text-muted-foreground text-sm">
												{{ rental.dispute.reason }}
											</p>
										</div>
										<div class="space-y-2">
											<p class="text-sm font-medium">Description:</p>
											<p class="text-muted-foreground text-sm">
												{{ rental.dispute.description }}
											</p>
										</div>
									</div>

									<!-- Admin's verdict -->
									<div class="space-y-2">
										<h4 class="font-medium">Admin's Decision</h4>
										<div class="space-y-2">
											<p class="text-sm">{{ rental.dispute.verdict }}</p>
											<p class="text-muted-foreground text-sm">
												{{ rental.dispute.verdict_notes }}
											</p>
										</div>
									</div>
								</div>
							</template>

							<!-- Always show status -->
							<div class="space-y-2">
								<h4 class="font-medium">Status</h4>
								<p
									class="text-sm"
									:class="{
										'text-muted-foreground': rental.dispute.status === 'pending',
										'text-primary': rental.dispute.status === 'reviewed',
										'text-destructive': rental.dispute.status === 'resolved',
									}"
								>
									{{
										rental.dispute.status.charAt(0).toUpperCase() +
										rental.dispute.status.slice(1)
									}}
								</p>
							</div>
						</div>
					</CardContent>
				</Card>
			</div>
		</div>
	</div>

	<!-- Accept Dialog -->
	<ConfirmDialog
		:show="showAcceptDialog"
		title="Approve Rental Request"
		:description="`Renter requested ${rental.quantity_requested} unit(s). Please specify how many units you want to approve.`"
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
		@cancel="showRejectDialog = false"
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

	<!-- Return Confirmation Dialog -->
	<ReturnConfirmationDialog
		v-model:show="showReturnDialog"
		:rental="rental"
		:type="returnDialogType"
	/>

	<!-- Add PaymentProofDialog component -->
	<PaymentProofDialog
		v-if="selectedPayment"
		:show="showPaymentProofDialog"
		:payment="selectedPayment"
		@update:show="showPaymentProofDialog = $event"
	/>

	<!-- Add new DisputeDialog component before the end of template -->
	<DisputeDialog v-model:show="showDisputeDialog" :rental="rental" />

	<!-- Add dialog at the end of the template -->
	<PickupScheduleDialog
		v-model:show="showScheduleDialog"
		:rental="rental"
		:userRole="userRole"
		:lenderSchedules="lenderSchedules"
	/>

	<!-- Add this at the end of the template with other dialogs -->
	<ReturnScheduleDialog
		v-model:show="showReturnScheduleDialog"
		:rental="rental"
		:userRole="userRole"
		:lenderSchedules="lenderSchedules"
	/>

	<!-- Add PickupScheduleDialog near other dialogs at bottom of template -->
	<PickupScheduleDialog
		v-model:show="showPickupDialog"
		:rental="rental"
		:userRole="userRole"
		:lenderSchedules="lenderSchedules"
	/>

	<!-- Early Return Dialog -->
	<ConfirmDialog
		v-model:show="showEarlyReturnDialog"
		title="Early Return Request"
		description="Please review these important details"
		confirmLabel="Yes, Start Return Process"
		cancelLabel="No, Keep Renting"
		:processing="initiateForm.processing"
		@confirm="proceedWithReturn"
		@cancel="showEarlyReturnDialog = false"
	>
		<div class="space-y-6 mb-4">
			<!-- Current Rental Status -->
			<div class="bg-amber-50 p-4 rounded-lg space-y-2">
				<p class="text-amber-600 text-sm font-medium">
					Original End Date: {{ formatDateTime(rental.end_date, "MMMM D, YYYY") }}
				</p>
				<p class="text-amber-700 text-sm font-semibold">
					{{ rental.remaining_days }} days remaining in your rental period
				</p>
				<p class="text-amber-600 text-sm">
					Non-refundable amount: {{ formatNumber((rental.remaining_days/rentalDays) * rentalOnlyTotal) }}
				</p>
			</div>

			<!-- Important Notices -->
			<div class="border border-destructive/20 bg-destructive/5 p-4 rounded-lg">
				<h4 class="font-medium text-sm text-destructive mb-2">⚠️ Important Notice:</h4>
				<ul class="space-y-2 text-sm text-destructive/90">
					<li class="flex items-start gap-2">
						<span>•</span>
						<span>This action <strong>cannot be undone</strong> once initiated</span>
					</li>
					<li class="flex items-start gap-2">
						<span>•</span>
						<span>No refunds will be provided for unused rental days</span>
					</li>
					<li class="flex items-start gap-2">
						<span>•</span>
						<span>Click "No, Keep Renting" if this was clicked by mistake</span>
					</li>
				</ul>
			</div>

			<!-- Process Steps -->
			<div class="space-y-4">
				<div class="flex items-start gap-3">
					<div class="mt-1 p-1.5 bg-primary/10 text-primary rounded-lg">
						<Package class="w-4 h-4" />
					</div>
					<div>
						<p class="font-medium text-sm">Next Steps</p>
						<p class="text-muted-foreground text-sm">
							1. Select a return schedule<br>
							2. Wait for lender confirmation<br>
							3. Return item during scheduled time
						</p>
					</div>
				</div>

				<div class="flex items-start gap-3">
					<div class="mt-1 p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
					</div>
					<div>
						<p class="font-medium text-sm">Security Deposit</p>
						<p class="text-muted-foreground text-sm">
							Your security deposit of {{ formatNumber(rental.deposit_fee) }} will be returned after verification
						</p>
					</div>
				</div>
			</div>

			<!-- Final Confirmation -->
			<div class="bg-muted/30 p-4 rounded-lg">
				<p class="text-sm font-medium text-center">
					Are you sure you want to proceed with the early return?
				</p>
				<p class="text-xs text-muted-foreground text-center mt-1">
					Click "No, Keep Renting" to continue with your rental as planned
				</p>
			</div>
		</div>
	</ConfirmDialog>
</template>
