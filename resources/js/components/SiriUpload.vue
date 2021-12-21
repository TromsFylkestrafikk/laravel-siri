<template>
  <div class="content">
    <h2>Simulate SIRI consumer post request</h2>
    <form class="siri-xml-form" @submit.prevent="submitXml">
      <div class="form-input form-file">
        <label for="siri-xml">Valid SIRI XML file.</label> <br>
        <input
          ref="xmlFile"
          type="file"
          class="siri-xml-file"
          name="siri-xml"
        >
      </div>
      <div class="form-input form-select">
        <label for="siri-select-channel">Use this subscription</label> <br>
        <select v-model="selectedId" class="siri-channel" name="siri-select-channel">
          <option
            v-for="subscription in subscriptions"
            :key="subscription.id"
            :value="subscription.id"
          >
            {{ subscription.channel }} – {{ subscription.subscription_url }}
          </option>
        </select>
      </div>
      <input type="submit" value="Emulate request">
    </form>
  </div>
</template>

<script src="./SiriUpload" />
