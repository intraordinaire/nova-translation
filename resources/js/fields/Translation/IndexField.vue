<template>
  <div class="nova-translation-field_index" v-if="field.value">
    <div
      v-for="otherLocale in otherLocales"
      :key="`translation_${otherLocale.id}`"
    >
      <router-link
        v-if="isTranslated[otherLocale.id]"
        class="inline-flex cursor-pointer no-underline text-3xl"
        :to="{
          name: 'detail',
          params: {
            resourceName,
            resourceId: translations[otherLocale.id].translatable_id,
          },
        }"
        :title="__('View')"
      >
        <span class="nova-translation--flag">
          {{ otherLocale.flag }}
        </span>
      </router-link>
    </div>
  </div>
</template>

<script>
import I18nMixin from '../../mixins/I18n'
import TranslationMixin from '../../mixins/Translation'
import CreateTranslationLink from './CreateTranslationLink'

export default {
  components: {
    CreateTranslationLink,
  },

  mixins: [
    I18nMixin,
    TranslationMixin,
  ],

  props: [
    'field',
    'resourceName',
    'resourceId',
  ],
}
</script>

<style lang="scss">
  .nova-translation-field_index {
    display: flex;
    align-items: center;

    div {
      margin-left: .5rem;

      &:first-child {
        margin-left: 0;
      }

      a {
        text-decoration: none;
      }
    }
  }
</style>
