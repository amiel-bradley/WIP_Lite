<script setup>
import { router } from '@inertiajs/vue3'

defineProps(['campaigns'])

const deleteCampaign = (campaign) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette campagne ?')) {
        router.delete(route('campaigns.destroy', campaign.id))
    }
}
</script>

<template>
    <div class="p-4">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                Gestion des campagnes
            </h2>

            <button
                @click="router.visit(route('campaigns.create'))"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
                Nouvelle campagne
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Début</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fin</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="campaign in campaigns" :key="campaign.id">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ campaign.name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ campaign.description }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ campaign.start_date }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ campaign.end_date }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span :class="[
                                'px-2 py-1 rounded text-xs',
                                campaign.status === 'active' ? 'bg-green-100 text-green-800' :
                                campaign.status === 'inactive' ? 'bg-gray-100 text-gray-800' :
                                'bg-blue-100 text-blue-800'
                            ]">
                                {{ campaign.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm flex gap-2">
                            <button
                                @click="router.visit(route('campaigns.show', campaign.id))"
                                class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                            >
                                Voir
                            </button>
                            <button
                                @click="router.visit(route('campaigns.edit', campaign.id))"
                                class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200"
                            >
                                Éditer
                            </button>
                            <button
                                @click="deleteCampaign(campaign)"
                                class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200"
                            >
                                Supprimer
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
