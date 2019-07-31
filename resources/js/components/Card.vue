<template>
  <div class="card">
    <div class="card-header">
      <slot name="title"></slot>
      <div class="card-options">
        <slot name="options"></slot>
      </div>
    </div>
    <div class="card-body">
      <slot name="body"></slot>
    </div>
  </div>
</template>

<script>
export default {
  
}
</script>