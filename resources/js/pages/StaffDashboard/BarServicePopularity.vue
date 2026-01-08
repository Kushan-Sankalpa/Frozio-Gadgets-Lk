<script setup>
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  LinearScale,
  CategoryScale
} from 'chart.js'

ChartJS.register(
  Title, Tooltip, Legend,
  BarElement, LinearScale, CategoryScale
)

const props = defineProps({
  labels: Array,
  data: Array
})

const chartData = {
  labels: props.labels,
  datasets: [
    {
      label: 'Services',
      data: props.data,
      backgroundColor: '#6366f1',
      borderRadius: 6
    }
  ]
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    x: {
      ticks: { color: '#64748b' }
    },
    y: {
      ticks: { color: '#64748b' },
      beginAtZero: true
    }
  }
}
</script>

<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>
