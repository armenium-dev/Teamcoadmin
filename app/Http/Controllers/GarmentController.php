<?php

namespace App\Http\Controllers;

use App\Garment;
use App\Http\Requests\StoreGarment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GarmentController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function index()
    {
        $garments = [];

        return view('garments.index', ['garments' => $garments]);
    }

    public function parts(Request $request)
    {
        $sort_cols = [
            0 => 'id',
            1 => 'code',
            2 => 'title',
            3 => 'description',
            4 => 'position',
        ];

        $query = Garment::query();

        $query->select('*');

        if (isset($request->search)) {
            if (!empty($request->search['value'])) {

                $phrase = $request->search['value'];
                $like_phrase = "%" . $phrase . "%";

                $query->where('id', '=', $phrase);
                $query->orWhere('code', 'like', $like_phrase);
                $query->orWhere('title', 'like', $like_phrase);
                $query->orWhere('description', 'like', $like_phrase);
            }
        }


        if (isset($request->order)) {
            foreach ($request->order as $order) {
                $query->orderBy($sort_cols[$order['column']], $order['dir']);
            }
        } else {
            $query->orderBy('position');
        }

        $query->offset($request->start);
        $query->limit($request->length);

        #dd($query->toSql());

        $data = $query->get();
        $total_count = $query->getQuery()->getCountForPagination();

        $rows = [];

        if ($data) {
            foreach ($data->all() as $item) {
                $checked = isset($item->color_autoupdate) && $item->color_autoupdate == 1 ? 'checked="checked"' : '';
                $rows[] = [
                    $item->id,
                    $item->code,
                    $item->title,
                    $item->description,
                    $item->position,
                    '<i class="fa fa-long-arrow-up"></i>  <i class="fa fa-long-arrow-down"></i>',
                    '<a href="' . route('garment.show', $item->id) . '" class="btn btn-warning text-dark" title="View"><i class="fa fa-eye"></i></a>',
                    '<a href="' . route('garment.edit', $item->id) . '" class="btn btn-info text-dark" title="Edit"><i class="fa fa-edit"></i></a>',
                    '<button class="btn btn-danger btn-remove" data-garment-id="' . $item->id . '" data-garment-name="' . $item->title . '" data-toggle="modal" data-target="#myModal" data-action="' . route('garment.destroy', $item->id) . '" title="Delete"><i class="fa fa-trash"></i></button>',
                ];
            }
        }

        $data = [
            'draw' => $request->draw,
            'recordsTotal' => $total_count,
            'recordsFiltered' => $total_count,
            'data' => $rows,
        ];

        #dd($rows);

        return response()->json($data, 200);
    }

    public function create()
    {
        return view('garments.create');
    }

    public function store(StoreGarment $request)
    {
        #dd($request->all());
        $time = time();
        $mainImagePath = $sizeImagePath = null;

        if ($request->hasFile('mainImage')) {
            $extension = $request->mainImage->extension();
            $fileName = $time . '_' . Str::random(8) . '.' . $extension;
            $mainImagePath = $request->mainImage->storeAs('public/garments', $fileName);
        }

        if ($request->hasFile('sizeImage')) {
            $extension = $request->sizeImage->extension();
            $fileName = $time . '_' . Str::random(8) . '.' . $extension;
            $sizeImagePath = $request->sizeImage->storeAs('public/garments', $fileName);
        }

        $request->merge(['main_image' => $mainImagePath, 'size_image' => $sizeImagePath]);

        Garment::create($request->all());

        return redirect('garment/create')->with('status', 'Garment Type created');
    }

    public function show($id)
    {
        $garment = Garment::findOrFail($id);

        return view('garments.show', compact('garment'));
    }

    public function edit($id)
    {
        $garment = Garment::findOrFail($id);

        return view('garments.edit', compact('garment'));
    }

    public function update(StoreGarment $request, Garment $garment)
    {
        $time = time();

        if ($request->hasFile('mainImage')) {
            $extension = $request->mainImage->extension();
            $fileName = $time . '_' . Str::random(8) . '.' . $extension;
            $mainImagePath = $request->mainImage->storeAs('public/garments', $fileName);

            $request->merge(['main_image' => $mainImagePath]);
        }

        if ($request->hasFile('sizeImage')) {
            $extension = $request->sizeImage->extension();
            $fileName = $time . '_' . Str::random(8) . '.' . $extension;
            $sizeImagePath = $request->sizeImage->storeAs('public/garments', $fileName);

            $request->merge(['size_image' => $sizeImagePath]);
        }

        $garment->update($request->all());

        return redirect('garment/' . $garment->id . '/edit')->with('status', 'Garment Type updated');
    }

    /*public function order(Request $request)
    {
		foreach($request->position as $key => $value){
			Garment::where('id', $key)->update(['position' => $value]);
		}

		return response('Update Succesfully', 200);
    }*/

    public function order(Request $request)
    {
        $positions = $request->positions; // Получаем массив позиций
        //dd($positions);
        // Проверяем, что данные получены
        if (empty($positions)) {
            return response('No positions data received', 400);
        }

        DB::transaction(function () use ($positions) {
            foreach ($positions as $position) {
                // Проверяем наличие обязательных полей
                /*if (!isset($position['id'])) {
                    throw new \Exception('Missing garment ID in position data');
                }*/

                Garment::where('id', $position['id'])
                    ->update(['position' => $position['newPosition']]);
            }
        });

        return response('Update Successfully', 200);
    }
}
