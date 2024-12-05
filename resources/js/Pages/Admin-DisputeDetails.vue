<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Archive } from "lucide-vue-next";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar";

// Mock data
const dispute = ref({
  id: 'D001',
  username: 'John Doe',
  toolName: 'Bosch Power Drill',
  toolId: 'T123',
  rentalId: 'R456',
  renter: 'Jane Smith',
  amount: 1500,
  reason: 'Late Return',
  category: 'Time-related Disputes',
  complaint: 'The tool was returned 3 days after the agreed return date without prior notice.',
  issuedOn: '2024-03-15',
  status: 'In Progress',
  resolution: {
    inFavorOf: 'John Doe',
    amount: 500
  }
});

const resolutionSteps = ref([
  {
    user: 'John Doe',
    date: '2024-03-15 14:30',
    description: 'Dispute filed for late return of tool.',
    avatar: 'https://picsum.photos/200'
  },
  {
    user: 'Support Team',
    date: '2024-03-16 09:15',
    description: 'Review initiated. Contacting involved parties.',
    avatar: 'https://picsum.photos/201'
  },
  {
    user: 'Jane Smith',
    date: '2024-03-16 11:30',
    description: 'Response received from renter explaining the delay.',
    avatar: 'https://picsum.photos/202'
  }
]);
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left Column -->
    <Card>
      <CardHeader>
        <CardTitle class = "mb-3">Dispute Details</CardTitle>

        <Separator />
      </CardHeader>
      <CardContent class="space-y-6">
        <div>
          <h3 class="text-sm font-medium text-muted-foreground mb-2">Dispute Initiator</h3>
          <p class="text-lg font-semibold">{{ dispute.username }}</p>
        </div>

        <Separator />

        <div>
          <h3 class="text-sm font-medium text-muted-foreground mb-4">Item Details</h3>
          <div class="space-y-4">
            <p class="text-lg font-semibold">{{ dispute.toolName }}</p>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-muted-foreground mb-3">Tool ID</p>
                <p class="font-medium">{{ dispute.toolId }}</p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground mb-3">Rental ID</p>
                <p class="font-medium">{{ dispute.rentalId }}</p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground mb-3">Renter</p>
                <p class="font-medium">{{ dispute.renter }}</p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground mb-3">Transaction Amount</p>
                <p class="font-medium">₱{{ dispute.amount }}</p>
              </div>
            </div>

            <div>
              <p class="text-sm text-muted-foreground mb-3">Dispute Reason</p>
              <p class="font-medium">{{ dispute.reason }}</p>
            </div>

            <div>
              <p class="text-sm text-muted-foreground mb-3">Type of Dispute</p>
              <p class="font-medium">{{ dispute.category }}</p>
            </div>

            <div>
              <p class="text-sm text-muted-foreground mb-3">Complaint</p>
              <p class="text-sm mt-1">{{ dispute.complaint }}</p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Right Column -->
    <Card>
      <CardHeader>
        <CardTitle class = "mb-3">Resolution</CardTitle>
        <Separator />
      </CardHeader>
      <CardContent class="space-y-6">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <p class="text-sm text-muted-foreground mb-3">Dispute ID</p>
            <p class="font-medium">{{ dispute.id }}</p>
          </div>
          <div>
            <p class="text-sm text-muted-foreground mb-3">Issued On</p>
            <p class="font-medium">{{ dispute.issuedOn }}</p>
          </div>
          <div>
            <p class="text-sm text-muted- mb-3">Status</p>
            <p class="font-medium">{{ dispute.status }}</p>
          </div>
        </div>

        <div class="bg-muted/50 p-4 rounded-lg">
          <p class="text-sm text-muted-foreground">Resolution Reached</p>
          <p class="font-medium mt-1">In favor of {{ dispute.resolution.inFavorOf }}</p>
          <p class="text-sm mt-1">Amount Charged: ₱{{ dispute.resolution.amount }}</p>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-semibold">Resolution Process</h3>
            <p class="text-sm text-muted-foreground">Below are the dispute details and resolution</p>
          </div>
          <Button variant="outline" size="icon">
            <Archive class="h-4 w-4" />
          </Button>
        </div>

        <div class="space-y-4">
          <div v-for="(step, index) in resolutionSteps" :key="index" class="bg-card border rounded-lg p-4">
            <div class="flex items-start justify-between">
              <div class="flex items-start space-x-4">
                <Avatar>
                  <AvatarImage :src="step.avatar" />
                  <AvatarFallback>{{ step.user[0] }}</AvatarFallback>
                </Avatar>
                <div>
                  <p class="font-medium">{{ step.user }}</p>
                  <p class="text-sm text-muted-foreground">{{ step.date }}</p>
                  <p class="text-sm mt-2">{{ step.description }}</p>
                </div>
              </div>
              <Button variant="outline" size="sm">View Details</Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>