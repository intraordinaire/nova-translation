<?php

namespace BBSLab\NovaTranslation\Http\Controllers\TranslatableResource;

use BBSLab\NovaTranslation\Models\Locale;
use BBSLab\NovaTranslation\Resources\TranslatableResource;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\ActionEvent;
use Laravel\Nova\Http\Controllers\ResourceStoreController;
use Laravel\Nova\Http\Requests\CreateResourceRequest;

class StoreController extends ResourceStoreController
{
    use Traits\TranslatableController;

    /**
     * {@inheritdoc}
     */
    public function handle(CreateResourceRequest $request)
    {
        $resource = $request->resource();

        if (is_subclass_of($resource, TranslatableResource::class)) {
            $resource::authorizeToCreate($request);
            // @TODO... Ensure creation rules
            $resource::validateForCreation($request);

            $model = DB::transaction(function () use ($request, $resource) {
                $models = $this->storeTranslatable($request, $resource);

                /** @var \BBSLab\NovaTranslation\Models\Locale $currentLocale */
                $currentLocale = Locale::query()->select('id')->where('iso', '=', app()->getLocale())->first();

                return $models[$currentLocale->id];
            });

            return response()->json([
                'id' => $model->getKey(),
                'resource' => $model->attributesToArray(),
                'redirect' => $resource::redirectAfterCreate($request, $request->newResourceWith($model)),
            ], 201);
        } else {
            return parent::handle($request);
        }
    }

    /**
     * Store translatable.
     *
     * @param  \Laravel\Nova\Http\Requests\CreateResourceRequest  $request
     * @param  string  $resource
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function storeTranslatable(CreateResourceRequest $request, string $resource)
    {
        $model = $resource::newModel();
        $creationFields = (new $resource($model))->creationFields($request);
        $translatedData = $this->mapTranslatableData($request, $creationFields);

        $translatedModels = [];
        foreach ($translatedData as $localeId => $fields) {
            $translationId = $fields['translation_id'];
            unset($fields['translation_id']);

            $translatedModels[$localeId] = $resource::newModel();
            foreach ($fields as $field => $value) {
                $translatedModels[$localeId]->$field = $value;
            }

            if ($request->viaRelationship()) {
                // @TODO... Check for Translation entry for new created model
                $request->findParentModelOrFail()->{$request->viaRelationship}()->save($translatedModels[$localeId]);
            } else {
                $translatedModels[$localeId]->save();
                $translatedModels[$localeId]->upsertTranslationEntry($localeId, $translationId);
            }

            ActionEvent::forResourceCreate($request->user(), $translatedModels[$localeId])->save();

            // @TODO...
            // collect($callbacks)->each->__invoke();
        }

        return $translatedModels;
    }
}
