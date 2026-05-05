<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps(['campaign'])

const form = ref({
    name: props.campaign.name,
    description: props.campaign.description,
    start_date: props.campaign.start_date,
    end_date: props.campaign.end_date,
    status: props.campaign.status
})

const updateCampaign = () => {
    router.put(route('campaigns.update', props.campaign.id), form.value)
}
</script>

<template>
    <div class="p-4 max-w-2xl mx-auto">

        <h2 class="text-xl font-bold mb-6">Modifier la campagne</h2>

        <form @submit.prevent="updateCampaign" class="flex flex-col gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                <input
                    v-model="form.start_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                <input
                    v-model="form.end_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select
                    v-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="finished">Terminée</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button
                    type="button"
                    @click="router.visit(route('campaigns.show', campaign.id))"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                >
                    Annuler
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Mettre à jour
                </button>
            </div>

        </form>

    </div>
</template>
