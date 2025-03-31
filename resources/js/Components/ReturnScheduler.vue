<script setup>
import { ref, computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { useForm } from "@inertiajs/vue3";
import { formatDateTime } from "@/lib/formatters";
import { format } from "date-fns";
import { formatNumber } from "@/lib/formatters";
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import PayOverdueDialog from '@/Components/PayOverdueDialog.vue';
import ReturnProofDialog from '@/Components/ReturnProofDialog.vue';

const props = defineProps({
  rental: Object,
  userRole: String,
  lenderSchedules: Array,
});

const initiateForm = useForm({});
const confirmForm = useForm({});

// Dialog state
const showEarlyReturnDialog = ref(false);

// Available schedules computation
const availableSchedules = computed(() => {
  if (!props.rental.end_date || !props.lenderSchedules?.length) return [];
  
  const endDate = new Date(props.rental.end_date);
  endDate.setHours(0, 0, 0, 0);

  return props.lenderSchedules
    .map(schedule => ({
      ...schedule,
      scheduleDate: getScheduleDate(schedule.day_of_week),
      formattedTime: formatScheduleTime(schedule)
    }))
    .filter(schedule => schedule.scheduleDate >= endDate)
    .sort((a, b) => a.scheduleDate - b.scheduleDate);
});

// Helper functions for date/time formatting
const getScheduleDate = (dayOfWeek) => {
  const today = new Date();
  const daysMap = {
    'Monday': 1, 'Tuesday': 2, 'Wednesday': 3,
    'Thursday': 4, 'Friday': 5, 'Saturday': 6, 'Sunday': 0
  };
  
  let daysToAdd = daysMap[dayOfWeek] - today.getDay();
  if (daysToAdd <= 0) daysToAdd += 7;
  
  const scheduleDate = new Date(today);
  scheduleDate.setDate(today.getDate() + daysToAdd);
  return scheduleDate;
};

const formatScheduleTime = (schedule) => {
  const formatTimeString = (timeStr) => {
    if (!timeStr) return '';
    const [hours, minutes] = timeStr.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', { 
      hour: 'numeric',
      minute: '2-digit',
      hour12: true 
    });
  };

  return `${formatTimeString(schedule.start_time)} to ${formatTimeString(schedule.end_time)}`;
};

// Handle schedule confirmation by lender
const handleConfirmSchedule = () => {
  if (!selectedSchedule.value) return;
  
  confirmForm.patch(route('return-schedules.confirm', {
    rental: props.rental.id
  }), {
    preserveScroll: true,
    onSuccess: () => {
      // Show success state is handled by template through confirmedSchedule computed
      console.log('Schedule confirmed successfully');
    }
  });
};

// Computed properties for visibility control
const showSchedulePicker = computed(() => {
  if (props.rental.is_overdue) {
    if (!hasVerifiedOverduePayment.value) return false;
  }
  
  return props.rental.status === 'pending_return' && 
         props.userRole === 'renter' && 
         !props.rental.return_schedules?.some(s => s.is_selected);
});

const selectedSchedule = computed(() => {
  if (!props.rental.return_schedules?.length) return null;
  return props.rental.return_schedules.find(s => s.is_selected);
});

const confirmedSchedule = computed(() => {
  if (!props.rental.return_schedules?.length) return null;
  return props.rental.return_schedules.find(s => s.is_confirmed);
});

// Add computed properties for payment states
const paymentRequest = computed(() => 
  props.rental.payment_request?.type === 'overdue' ? props.rental.payment_request : null
);

const paymentStatus = computed(() => paymentRequest.value?.status || null);

const hasPendingOverduePayment = computed(() => paymentStatus.value === 'pending');
const hasVerifiedOverduePayment = computed(() => paymentStatus.value === 'verified');
const hasRejectedOverduePayment = computed(() => paymentStatus.value === 'rejected');

// Add computed for return states
const canInitiateReturn = computed(() => false);

const showWaitingMessage = computed(() => 
  props.rental.status === 'pending_return' && 
  props.userRole === 'lender' && 
  !props.rental.return_schedules?.some(s => s.is_selected)
);

// Add ref for dialogs
const showOverduePayment = ref(false);
const showReturnProofDialog = ref(false);
const returnProofType = ref('submit');

// Add computed for selected schedule details
const selectedScheduleDetails = computed(() => {
  if (!selectedSchedule.value) return null;

  const scheduleDate = new Date(selectedSchedule.value.return_datetime);
  return {
    dayOfWeek: format(scheduleDate, 'EEEE'),
    date: format(scheduleDate, 'MMMM d, yyyy'),
    timeFrame: `${formatTimeString(selectedSchedule.value.start_time)} to ${formatTimeString(selectedSchedule.value.end_time)}`
  };
});

// Add helper function for time formatting
const formatTimeString = (timeStr) => {
  if (!timeStr) return '';
  const [hours, minutes] = timeStr.split(':');
  const date = new Date();
  date.setHours(parseInt(hours), parseInt(minutes));
  return date.toLocaleTimeString('en-US', { 
    hour: 'numeric',
    minute: '2-digit',
    hour12: true 
  });
};

// Add handlers for return proof actions
const handleSubmitReturn = () => {
  returnProofType.value = 'submit';
  showReturnProofDialog.value = true;
};

const handleConfirmReturn = () => {
  returnProofType.value = 'confirm';
  showReturnProofDialog.value = true;
};

// Update the schedule confirmation message computed
const scheduleConfirmationMessage = computed(() => {
  if (!confirmedSchedule.value) return null;

  const date = format(new Date(confirmedSchedule.value.return_datetime), 'MMMM d, yyyy');
  const time = `${formatTimeString(confirmedSchedule.value.start_time)} to ${formatTimeString(confirmedSchedule.value.end_time)}`;
  
  if (props.userRole === 'lender') {
    return {
      title: '✓ Return Schedule Confirmed',
      message: `The renter will return the item on ${date} between ${time}.`
    };
  }
  return {
    title: '✓ Return Schedule Confirmed',
    message: `Please return the item on ${date} between ${time} at ${props.rental.listing.location.address}`,
    note: 'Remember to take photos during handover for return proof.'
  };
});

// Update the computed property for selected schedule info
const selectedScheduleInfo = computed(() => {
  if (!selectedSchedule.value) return null;

  const date = format(new Date(selectedSchedule.value.return_datetime), 'MMMM d, yyyy');
  const time = `${formatTimeString(selectedSchedule.value.start_time)} to ${formatTimeString(selectedSchedule.value.end_time)}`;
  
  if (props.userRole === 'lender') {
    return {
      title: "Return Schedule Selected",
      message: `The renter plans to return on ${date} between ${time}`,
      note: "Please confirm this schedule to proceed with the return process.",
      status: selectedSchedule.value.is_confirmed ? 'confirmed' : 'pending'
    };
  }
  
  return {
    title: "Return Schedule Selected",
    message: `You selected to return on ${date} between ${time}`,
    note: "Please wait for the lender to confirm this schedule.",
    status: selectedSchedule.value.is_confirmed ? 'confirmed' : 'pending'
  };
});
</script>

<template>
  <Card v-if="rental.status === 'pending_return' || rental.status === 'return_scheduled' || rental.status === 'pending_return_confirmation'" class="shadow-sm">
    <CardHeader>
      <CardTitle>Return Process</CardTitle>
    </CardHeader>
    <CardContent>
      <!-- Show overdue states -->
      <div v-if="rental.is_overdue && rental.status === 'active'" class="space-y-4">
        <!-- Different views for renter -->
        <template v-if="userRole === 'renter'">
          <!-- Initial unpaid state -->
          <template v-if="!paymentRequest">
            <Alert variant="destructive">
              <AlertDescription class="space-y-2">
                <p>This rental is overdue. Please pay the overdue fees to proceed with the return process.</p>
                <p class="font-medium">Overdue Fee: {{ formatNumber(rental.overdue_fee) }}</p>
              </AlertDescription>
            </Alert>
            
            <div class="flex gap-2">
              <Button 
                variant="default" 
                @click="showOverduePayment = true"
              >
                Pay Overdue Fees
              </Button>
              <Button 
                variant="outline" 
                disabled
              >
                Initiate Return
              </Button>
            </div>
          </template>

          <!-- Pending verification state -->
          <template v-else-if="hasPendingOverduePayment">
            <Alert variant="warning">
              <AlertDescription class="space-y-2">
                <p>Your overdue payment has been submitted and is pending verification.</p>
                <p class="font-medium mt-2">
                  Reference Number: {{ paymentRequest.reference_number }}
                </p>
                <p class="font-medium">Amount Paid: {{ formatNumber(rental.overdue_fee) }}</p>
              </AlertDescription>
            </Alert>
            <div class="mt-4 p-4 bg-muted rounded-lg">
              <p class="text-sm text-muted-foreground text-center">
                Please wait while we verify your payment. You will be notified once verified.
              </p>
            </div>
          </template>

          <!-- Rejected payment state -->
          <template v-else-if="hasRejectedOverduePayment">
            <Alert variant="destructive">
              <AlertDescription class="space-y-2">
                <p>Your overdue payment was rejected for the following reason:</p>
                <p class="font-medium mt-2">{{ paymentRequest.admin_feedback }}</p>
                <p class="mt-2">Please submit a new payment with the correct details.</p>
              </AlertDescription>
            </Alert>
            
            <div class="flex gap-2 mt-4">
              <Button 
                variant="default" 
                @click="showOverduePayment = true"
              >
                Submit Overdue Payment
              </Button>
              <Button 
                variant="outline" 
                disabled
              >
                Initiate Return
              </Button>
            </div>
          </template>

          <!-- Verified payment state -->
          <template v-else-if="hasVerifiedOverduePayment">
            <Alert variant="success">
              <AlertDescription class="space-y-2">
                <p>Your overdue payment has been verified successfully!</p>
                <p class="font-medium">Payment Details:</p>
                <ul class="space-y-1 mt-2">
                  <li>Reference: {{ paymentRequest.reference_number }}</li>
                  <li>Amount: {{ formatNumber(rental.overdue_fee) }}</li>
                  <li>Verified: {{ formatDateTime(paymentRequest.verified_at) }}</li>
                </ul>
              </AlertDescription>
            </Alert>
          </template>
        </template>

        <!-- Lender view for overdue states -->
        <template v-else>
          <template v-if="!paymentRequest">
            <Alert variant="warning">
              <AlertDescription class="space-y-2">
                <p>This rental is overdue. Waiting for the renter to submit the overdue payment.</p>
                <p class="font-medium">Outstanding Fee: {{ formatNumber(rental.overdue_fee) }}</p>
              </AlertDescription>
            </Alert>
          </template>

          <template v-else-if="hasPendingOverduePayment">
            <Alert variant="warning">
              <AlertDescription class="space-y-2">
                <p>The renter has submitted an overdue payment.</p>
                <p class="font-medium mt-2">
                  Reference Number: {{ paymentRequest.reference_number }}
                </p>
                <p class="font-medium">Amount: {{ formatNumber(rental.overdue_fee) }}</p>
              </AlertDescription>
            </Alert>
            <div class="mt-4 p-4 bg-muted rounded-lg">
              <p class="text-sm text-muted-foreground text-center">
                Please wait for admin verification before proceeding with the return process.
              </p>
            </div>
          </template>

          <!-- Add rejected payment state for lender -->
          <template v-else-if="hasRejectedOverduePayment">
            <Alert variant="destructive">
              <AlertDescription class="space-y-2">
                <p>The renter's overdue payment was rejected by admin:</p>
                <p class="font-medium mt-2">{{ paymentRequest.admin_feedback }}</p>
                <p class="mt-2">The renter will need to submit a new payment.</p>
                <p class="font-medium mt-2">Outstanding Fee: {{ formatNumber(rental.overdue_fee) }}</p>
              </AlertDescription>
            </Alert>
            <div class="mt-4 p-4 bg-muted rounded-lg">
              <p class="text-sm text-muted-foreground text-center">
                Waiting for the renter to submit a new payment...
              </p>
            </div>
          </template>

          <template v-else-if="hasVerifiedOverduePayment">
            <Alert variant="success">
              <AlertDescription class="space-y-2">
                <p>The renter's overdue payment has been verified.</p>
                <p>The return process can begin once the renter initiates it.</p>
                <p class="font-medium mt-2">
                  Verified Payment Details:
                </p>
                <ul class="space-y-1 mt-1">
                  <li>Reference: {{ paymentRequest.reference_number }}</li>
                  <li>Amount: {{ formatNumber(rental.overdue_fee) }}</li>
                </ul>
              </AlertDescription>
            </Alert>
          </template>
        </template>
      </div>

      <!-- Selected Schedule Information -->
      <div v-if="selectedSchedule && !confirmedSchedule" class="space-y-4">
        <Alert :variant="selectedScheduleInfo.status === 'confirmed' ? 'success' : 'info'">
          <AlertDescription class="space-y-2">
            <p class="font-medium">{{ selectedScheduleInfo.title }}</p>
            <p>{{ selectedScheduleInfo.message }}</p>
            <p class="text-xs text-muted-foreground mt-2">
              {{ selectedScheduleInfo.note }}
            </p>
          </AlertDescription>
        </Alert>

        <!-- Action Items Box -->
        <div class="bg-muted/30 p-4 rounded-lg space-y-2">
          <p class="font-medium text-sm">Next Steps:</p>
          <template v-if="userRole === 'renter'">
            <p class="text-sm text-muted-foreground">
              • Waiting for lender to confirm schedule
            </p>
            <p class="text-sm text-muted-foreground">
              • Once confirmed, you'll receive a notification
            </p>
            <p class="text-sm text-muted-foreground">
              • Return the item at {{ rental.listing.location.address }}
            </p>
          </template>
          <template v-else>
            <p class="text-sm text-muted-foreground">
              • Review the proposed schedule
            </p>
            <p class="text-sm text-muted-foreground">
              • Confirm if the schedule works for you
            </p>
            <p class="text-sm text-muted-foreground">
              • You'll meet at {{ rental.listing.location.address }}
            </p>
          </template>
        </div>
      </div>

      <!-- Waiting message section -->
      <div v-if="rental.status === 'pending_return' && !selectedSchedule" class="space-y-4">
        <template v-if="userRole === 'lender'">
          <Alert variant="info">
            <AlertDescription class="space-y-2">
              <p class="font-medium">A return has been initiated</p>
              <p class="text-muted-foreground">
                The renter has initiated the return process. Please wait for them to select a return schedule that works for both parties.
              </p>
            </AlertDescription>
          </Alert>
          <div class="p-4 text-center text-muted-foreground bg-muted/30 rounded-lg">
            Waiting for renter to select a return schedule...
          </div>
        </template>

        <template v-else>
          <Alert variant="info">
            <AlertDescription class="space-y-3">
              <div class="space-y-1">
                <p class="font-medium">Return Process Initiated</p>
                <p class="text-muted-foreground">The lender has been notified of your intention to return.</p>
              </div>
              <div class="space-y-2">
                <p class="font-medium">Next Steps:</p>
                <ol class="text-sm space-y-1 text-muted-foreground">
                  <li class="flex items-start gap-2">
                    <span class="font-medium text-primary">1.</span>
                    <span>Select a return schedule from the available time slots</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="font-medium text-primary">2.</span>
                    <span>Wait for the lender to confirm your selected schedule</span>
                  </li>
                  <li class="flex items-start gap-2">
                    <span class="font-medium text-primary">3.</span>
                    <span>Return the item during the confirmed schedule</span>
                  </li>
                </ol>
              </div>
            </AlertDescription>
          </Alert>
        </template>
      </div>

      <!-- Confirmed Schedule Display -->
      <Alert v-if="confirmedSchedule" variant="success" class="space-y-2">
        <AlertDescription>
          <div class="flex items-start gap-2">
            <h4 class="font-medium">{{ scheduleConfirmationMessage.title }}</h4>
          </div>
          <div class="space-y-1 mt-1">
            <p>{{ scheduleConfirmationMessage.message }}</p>
            <p v-if="userRole === 'renter'" class="text-xs text-muted-foreground">
              {{ scheduleConfirmationMessage.note }}
            </p>
          </div>
        </AlertDescription>
      </Alert>

      <!-- Return Proof Dialog -->
      <ReturnProofDialog
        v-model:show="showReturnProofDialog"
        :rental="rental"
        :type="returnProofType"
      />
    </CardContent>
  </Card>

  <!-- Only show payment dialog for renters and when payment is not pending -->
  <PayOverdueDialog
    v-if="userRole === 'renter' && !hasPendingOverduePayment"
    v-model:show="showOverduePayment"
    :rental="rental"
  />
</template>