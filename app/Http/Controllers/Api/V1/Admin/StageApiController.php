<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreStageRequest;
use App\Http\Requests\UpdateStageRequest;
use App\Http\Resources\Admin\StageResource;
use App\Models\Stage;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StageApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('stage_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new StageResource(Stage::with(['business_process'])->get());
    }

    public function store(StoreStageRequest $request)
    {
        $stage = Stage::create($request->all());

        foreach ($request->input('document', []) as $file) {
            $stage->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection('document');
        }

        return (new StageResource($stage))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Stage $stage)
    {
        abort_if(Gate::denies('stage_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new StageResource($stage->load(['business_process']));
    }

    public function update(UpdateStageRequest $request, Stage $stage)
    {
        $stage->update($request->all());

        if (count($stage->document) > 0) {
            foreach ($stage->document as $media) {
                if (!in_array($media->file_name, $request->input('document', []))) {
                    $media->delete();
                }
            }
        }
        $media = $stage->document->pluck('file_name')->toArray();
        foreach ($request->input('document', []) as $file) {
            if (count($media) === 0 || !in_array($file, $media)) {
                $stage->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection('document');
            }
        }

        return (new StageResource($stage))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Stage $stage)
    {
        abort_if(Gate::denies('stage_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stage->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
