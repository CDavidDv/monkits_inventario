<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="items-center px-4 py-2 bg-white border border-transparent rounded-sm text-sm text-black uppercase font-extrabold hover:bg-gray-600 focus:bg-gray-600 active:bg-lime-800 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
