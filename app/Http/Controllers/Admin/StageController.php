<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyStageRequest;
use App\Http\Requests\StoreStageRequest;
use App\Http\Requests\UpdateStageRequest;
use App\Models\BusinessProcess;
use App\Models\Stage;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class StageController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('stage_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stages = Stage::with(['business_process', 'media'])->get();

        return view('admin.stages.index', compact('stages'));
    }

    public function create()
    {
        abort_if(Gate::denies('stage_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $business_processes = BusinessProcess::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.stages.create', compact('business_processes'));
    }

    public function store(StoreStageRequest $request)
    {
        $stage = Stage::create($request->all());

        foreach ($request->input('document', []) as $file) {
            $stage->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection('document');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $stage->id]);
        }

        return redirect()->route('admin.stages.index');
    }

    public function edit(Stage $stage)
    {
        abort_if(Gate::denies('stage_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $business_processes = BusinessProcess::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $stage->load('business_process');

        return view('admin.stages.edit', compact('business_processes', 'stage'));
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

        return redirect()->route('admin.stages.index');
    }

    public function show(Stage $stage)
    {
        abort_if(Gate::denies('stage_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stage->load('business_process');

        return view('admin.stages.show', compact('stage'));
    }

    public function destroy(Stage $stage)
    {
        abort_if(Gate::denies('stage_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stage->delete();

        return back();
    }

    public function massDestroy(MassDestroyStageRequest $request)
    {
        Stage::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('stage_create') && Gate::denies('stage_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Stage();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
