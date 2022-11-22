<template>
  <div class="content">
    <h2>Simulate SIRI consumer post request</h2>
    <form class="siri-xml-form">
      <div class="form-input form-file">
        <label for="siri-xml">Valid SIRI XML files.</label> <br>
        <input
          ref="xmlFile"
          type="file"
          multiple="multiple"
          class="siri-xml-file"
          name="siri-xml"
        >
      </div>
      <div class="form-input form-select">
        <label for="">Delay between post requests</label>
        <select v-model="uploadDelay">
          <option v-for="delay in delayOptions" :key="delay" :value="delay">
            {{ delay }} seconds
          </option>
        </select>
      </div>
      <div class="form-input form-select">
        <label for="siri-select-channel">Use this subscription</label> <br>
        <select v-model="selectedId" class="siri-channel" name="siri-select-channel">
          <option
            v-for="(subscription, id) in subscriptions"
            :key="id"
            :value="id"
          >
            {{ subscription.channel }} – {{ subscription.subscription_url }}
          </option>
        </select>
      </div>
      <button :disabled="uploadInProgress" @click.prevent="submitXml">Emulate request</button>
      <button v-if="uploadInProgress" @click.prevent="cancelUpload = true">
        Avbryt
      </button>
    </form>
    <div v-if="uploadInProgress">
      Uploading file {{ currentFileIndex }} of {{ fileCount }}:
      <em>{{ currentFilename }}</em>
    </div>
    <div v-if="response" class="Result">
      <ul>
        <li>Status: {{ response.status }} ({{ response.statusText }})</li>
        <li>Response body: {{ response.response }}</li>
      </ul>
    </div>
  </div>
</template>

<script src="./SiriUpload" />
