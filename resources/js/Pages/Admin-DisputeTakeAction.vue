<script setup>
import { ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/ui/avatar";
import { Select, SelectTrigger, SelectContent, SelectItem, SelectValue } from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";

const selectedVerdict = ref('');
const selectedParty = ref('');
const charges = ref('');
const remarks = ref('');

const dispute = ref({
  status: 'Pre Arbitration',
  stage: 'In Progress',
  timestamp: '2024-03-20 14:30',
  initiator: {
    username: 'John Doe',
    avatar: 'https://picsum.photos/200',
    action: {
      date: '2024-03-20 14:30',
      description: 'Filed a dispute for damaged item.',
    },
    proofs: [
      'https://picsum.photos/300/200?random=1',
      'https://picsum.photos/300/200?random=2',
      'https://picsum.photos/300/200?random=3'
    ]
  },
  respondent: {
    username: 'Jane Smith',
    avatar: 'https://picsum.photos/201',
    responseDate: '2024-03-21 09:15',
    status: 'Awaiting Response'
  }
});

const handleSubmit = () => {
  console.log({
    verdict: selectedVerdict.value,
    inFavorOf: selectedParty.value,
    charges: charges.value,
    remarks: remarks.value
  });
};
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left Column -->
    <Card class="p-6">
      <CardContent class="space-y-6">
        <!-- Status Row -->
        <div class="flex items-center justify-between bg-muted/50 p-4 rounded-lg">
          <div>
            <span class="text-sm font-medium">{{ dispute.status }}</span>
            <p class="text-sm text-muted-foreground">{{ dispute.stage }}</p>
          </div>
          <p class="text-sm text-muted-foreground">{{ dispute.timestamp }}</p>
        </div>

        <!-- Dispute Initiator -->
        <div>
          <h3 class="text-sm font-medium text-muted-foreground mb-4">Dispute Initiator</h3>
          <div class="flex items-center gap-4 mb-4">
            <Avatar class="h-12 w-12">
              <AvatarImage :src="dispute.initiator.avatar" />
              <AvatarFallback>{{ dispute.initiator.username[0] }}</AvatarFallback>
            </Avatar>
            <div>
              <p class="font-medium">{{ dispute.initiator.username }}</p>
            </div>
          </div>

          <!-- Action Card -->
          <div class="bg-card border rounded-lg p-4 mb-4">
            <p class="text-sm text-muted-foreground">{{ dispute.initiator.action.date }}</p>
            <p class="mt-1">{{ dispute.initiator.action.description }}</p>
          </div>

          <!-- Proof Images -->
          <h4 class="text-sm font-medium mb-2">Proof Uploaded</h4>
          <div class="grid grid-cols-3 gap-2">
            <img v-for="(proof, index) in dispute.initiator.proofs" 
                 :key="index" 
                 :src="proof" 
                 class="w-full h-24 object-cover rounded-lg"
            />
          </div>
        </div>

        <Separator />

        <!-- Dispute Respondent -->
        <div>
          <h3 class="text-sm font-medium text-muted-foreground mb-4">Dispute Respondent</h3>
          <div class="flex items-center gap-4 mb-4">
            <Avatar class="h-12 w-12">
              <AvatarImage :src="dispute.respondent.avatar" />
              <AvatarFallback>{{ dispute.respondent.username[0] }}</AvatarFallback>
            </Avatar>
            <div>
              <p class="font-medium">{{ dispute.respondent.username }}</p>
            </div>
          </div>

          <!-- Response Status Card -->
          <div class="bg-card border rounded-lg p-4 mb-4">
            <p class="text-sm text-muted-foreground">{{ dispute.respondent.responseDate }}</p>
            <p class="font-medium">{{ dispute.respondent.status }}</p>
          </div>

          <!-- Respondent's Justification -->
          <div class="space-y-2">
            <Label>Respondent's Reason/Justification</Label>
            <Textarea disabled placeholder="Awaiting response from the respondent..." />
          </div>

          <div class="mt-4">
            <Label>Proof Uploaded</Label>
            <p class="text-sm text-muted-foreground">No proof uploaded yet</p>
          </div>
        </div>

        <!-- Disclaimer -->
        <div class="bg-destructive/10 p-4 rounded-lg mt-6">
          <p class="text-sm text-destructive font-medium">
            Disclaimer: Maximum 7 days to pay the charge
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- Right Column -->
    <Card class="p-6">
      <CardHeader>
        <CardTitle>Actions</CardTitle>
      </CardHeader>
      <CardContent class="space-y-6">
        <!-- Verdict Selection -->
        <div class="space-y-2">
          <Label>Dispute Resolution Status</Label>
          <Select v-model="selectedVerdict">
            <SelectTrigger>
              <SelectValue placeholder="Choose verdict" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="resolved">Resolved</SelectItem>
              <SelectItem value="escalated">Escalated</SelectItem>
              <SelectItem value="dismissed">Dismissed</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Party Selection -->
        <div class="space-y-2">
          <h3 class="text-sm font-medium">In Favor Of:</h3>
          <RadioGroup v-model="selectedParty" class="grid grid-cols-2 gap-4">
            <div class="flex items-center space-x-2 mb-3">
              <RadioGroupItem value="renter" id="renter" />
              <Label for="renter">Renter</Label>
            </div>
            <div class="flex items-center space-x-2">
              <RadioGroupItem value="lender" id="lender" />
              <Label for="lender">Lender</Label>
            </div>
          </RadioGroup>
        </div>

        <!-- Charges -->
        <div class="space-y-2">
          <!-- Submit Button -->
            <Button class="" >
            Set charges
            </Button>
        </div>

        <!-- Remarks -->
        <div class="space-y-2">
          <Label>Remarks</Label>
          <Textarea 
            v-model="remarks"
            placeholder="Enter your remarks here..."
            rows="4"
          />
        </div>

        <!-- Submit Button -->
        <Button class="w-full" @click="handleSubmit">
          Submit Resolution
        </Button>
      </CardContent>
    </Card>
  </div>
</template>