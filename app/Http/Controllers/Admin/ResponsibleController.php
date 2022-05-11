<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyResponsibleRequest;
use App\Http\Requests\StoreResponsibleRequest;
use App\Http\Requests\UpdateResponsibleRequest;
use App\Models\Responsible;
use App\Models\Role;
use App\Models\Stage;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class ResponsibleController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('responsible_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $responsibles = Responsible::with(['stage', 'role', 'specific_user'])->get();

        return view('admin.responsibles.index', compact('responsibles'));
    }

    public function create()
    {
        abort_if(Gate::denies('responsible_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stages = Stage::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $roles = Role::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $specific_users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.responsibles.create', compact('roles', 'specific_users', 'stages'));
    }

    public function store(StoreResponsibleRequest $request)
    {
        $responsible = Responsible::create($request->all());

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $responsible->id]);
        }

        return redirect()->route('admin.responsibles.index');
    }

    public function edit(Responsible $responsible)
    {
        abort_if(Gate::denies('responsible_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $stages = Stage::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $roles = Role::pluck('title', 'id')->prepend(trans('global.pleaseSelect'), '');

        $specific_users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $responsible->load('stage', 'role', 'specific_user');

        return view('admin.responsibles.edit', compact('responsible', 'roles', 'specific_users', 'stages'));
    }

    public function update(UpdateResponsibleRequest $request, Responsible $responsible)
    {
        $responsible->update($request->all());

        return redirect()->route('admin.responsibles.index');
    }

    public function show(Responsible $responsible)
    {
        abort_if(Gate::denies('responsible_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $responsible->load('stage', 'role', 'specific_user');

        return view('admin.responsibles.show', compact('responsible'));
    }

    public function destroy(Responsible $responsible)
    {
        abort_if(Gate::denies('responsible_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $responsible->delete();

        return back();
    }

    public function massDestroy(MassDestroyResponsibleRequest $request)
    {
        Responsible::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('responsible_create') && Gate::denies('responsible_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Responsible();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
