<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps(['campaign'])
</script>

<template>
    <div class="p-4">

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                Détail campagne
            </h2>

            <div class="flex gap-2">
                <button
                    @click="router.visit(route('campaigns.index'))"
                    class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                >
                    Retour
                </button>
                <button
                    @click="router.visit(route('campaigns.edit', campaign.id))"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Modifier
                </button>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <p class="mb-2"><strong>Nom :</strong> {{ campaign.name }}</p>
            <p class="mb-2"><strong>Description :</strong> {{ campaign.description || 'Aucune description' }}</p>
            <p class="mb-2"><strong>Début :</strong> {{ campaign.start_date }}</p>
            <p class="mb-2"><strong>Fin :</strong> {{ campaign.end_date || 'Non spécifiée' }}</p>
            <p>
                <strong>Statut :</strong>
                <span :class="[
                    'ml-2 px-2 py-1 rounded text-xs',
                    campaign.status === 'active' ? 'bg-green-100 text-green-800' :
                    campaign.status === 'inactive' ? 'bg-gray-100 text-gray-800' :
                    'bg-blue-100 text-blue-800'
                ]">
                    {{ campaign.status }}
                </span>
            </p>
        </div>

        <h3 class="text-lg font-semibold mb-3">Employés affectés</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poste</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Manager</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-if="campaign.assignments && campaign.assignments.length > 0" v-for="assignment in campaign.assignments" :key="assignment.id">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ assignment.employee?.name || 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ assignment.position?.name || 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ assignment.manager?.name || 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-800">
                                {{ assignment.status }}
                            </span>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            Aucun employé affecté à cette campagne
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</template>
