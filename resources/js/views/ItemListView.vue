<script setup>
import { ref, onMounted } from 'vue'
import { listItems, createItem, deleteItem } from '../api/items'

const items = ref([])
const newName = ref('')
const newQuantity = ref(1)

async function loadItems() {
  const response = await listItems()
  items.value = response.data
}

async function addItem() {
  if (!newName.value) return
  await createItem({ name: newName.value, quantity: newQuantity.value })
  newName.value = ''
  newQuantity.value = 1
  await loadItems()
}

async function removeItem(id) {
  await deleteItem(id)
  await loadItems()
}

onMounted(loadItems)
</script>

<template>
  <div class="space-y-6">
    <section class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold mb-3">アイテムを追加</h2>
      <form @submit.prevent="addItem" class="flex gap-2">
        <input
          v-model="newName"
          type="text"
          placeholder="商品名"
          class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <input
          v-model.number="newQuantity"
          type="number"
          min="1"
          class="w-20 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
        <button
          type="submit"
          class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          追加
        </button>
      </form>
    </section>

    <section class="bg-white rounded-lg shadow">
      <h2 class="text-lg font-semibold p-4 border-b">リスト</h2>
      <ul v-if="items.length" class="divide-y">
        <li
          v-for="item in items"
          :key="item.id"
          class="flex items-center justify-between px-4 py-3"
        >
          <div>
            <span class="font-medium">{{ item.name }}</span>
            <span class="ml-2 text-sm text-gray-500">× {{ item.quantity }}</span>
          </div>
          <button
            @click="removeItem(item.id)"
            class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded"
          >
            削除
          </button>
        </li>
      </ul>
      <p v-else class="p-4 text-gray-500">アイテムがありません</p>
    </section>
  </div>
</template>
