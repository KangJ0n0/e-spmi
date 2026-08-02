<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Helper\StrukturHelper;
use App\Models\StrukturAnggota;

class StrukturAnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request){
        $filter= $request->input('filter');
        $inputpaginate= $request->input('paginate');
        $inputlimit= $request->input('limit');
        $inputstatus= $request->input('status');
        $query = DB::table('struktur_anggota')->select('id','nama','jabatan','status','tugas','foto')->when($filter,function($query) use($filter){
            $query->where('nama','like','%'.$filter.'%')
            ->orWhere('jabatan','like','%'.$filter.'%')
            ->orWhere('status','like','%'.$filter.'%');
        });
        if(!empty($inputstatus)){
            $query->where('status',$inputstatus);
        }
        $results = $inputpaginate === null
            ? ($inputlimit !== null ? $query->take($inputlimit)->get() : $query->get())
            : $query->paginate($inputpaginate);
        $collection = $results instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $results->getCollection()
            : $results;
        
        $collection->transform(function ($item) {
            $foto_link = $item->foto;

            if (File::exists(public_path($foto_link))) {
                $item->foto_url = URL::to($foto_link);
            }
            unset($item->foto);

            return $item;
        });

        if ($results instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $results->setCollection($collection);
        }
        return response()->json($results);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|max:100',
            'jabatan' => 'required|max:100',
            'status' => 'required|max:100',
            'tugas' => 'required|max:100',
            'urutan' => 'nullable|integer',
            

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $nama = $request->input('nama');
        $jabatan = $request->input('jabatan');
        $status = $request->input('status');
        $tugas = $request->input('tugas');
        $urutan = $request->input('urutan');
        $checknama = DB::table('struktur_anggota')->where('nama', $nama)->first();
        if ($checknama) {
            return response()->json(['error' => 'Nama sudah ada'], 400);
        }
        try {
            DB::beginTransaction();
            if ($request->has('foto') && !empty($request->input('foto'))) {
                $relativePath = StrukturHelper::saveImageBase64($request->input('foto'), 'FotoStruktur', Str::slug($nama));
                $foto_upload = $relativePath;
            }
        StrukturAnggota::create([
            'nama' => $nama,
            'jabatan' => $jabatan,
            'status' => $status,
            'tugas' => $tugas,
            'foto' => $foto_upload ?? null,
            'urutan' => $urutan ?? null
        ]);
        DB::commit();
                  return response()->json(['message' => "Sukses"], 200);
            }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal"], 400);
         }
     }



    /**
     * Display the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:struktur_anggota,id',
            'nama' => 'required|max:100',
            'jabatan' => 'required|max:100',
            'status' => 'required|max:100',
            'tugas' => 'required|max:100',
            'urutan' => 'required|integer',
            
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $id = $request->input('id');
        $nama = $request->input('nama');
        $jabatan = $request->input('jabatan');
        $status = $request->input('status');
        $tugas = $request->input('tugas');
        $urutan = $request->input('urutan');
        $checkData = StrukturAnggota::where('id', '=', $id, null)->first();
        if (!$checkData) {
            return response()->json(['error' => 'Error'], 404);
        }
        try {
            DB::beginTransaction();
             if ($request->has('foto') && !empty($request->input('foto'))) {
                $absolutePath = public_path($checkData->foto);
                if (File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }
                $relativePath = StrukturHelper::saveImageBase64($request->input('foto'), 'FotoStruktur', Str::slug($nama));
                $foto_upload = $relativePath;
            } else {
                $foto_upload = $checkData->foto;
            }
            StrukturAnggota::where('id', '=', $id, null)->update([
                'nama' => $nama,
                'jabatan' => $jabatan,
                'status' => $status,
                'tugas' => $tugas,
                'foto' => $foto_upload ?? null,
                'urutan' => $urutan ?? null
               
            ]);
            DB::commit();
            return response()->json(['message' => "Sukses"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:struktur_anggota,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $id = $request->input('id');
        $checkData = StrukturAnggota::where('id', '=', $id, null)->first();
        if (!$checkData) {
            return response()->json(['error' => 'Error'], 404);
        }
        try {
            DB::beginTransaction();
            if ($checkData->foto && File::exists(public_path($checkData->foto))) {
                File::delete(public_path($checkData->foto));
            }
            StrukturAnggota::where('id', '=', $id, null)->delete();
            DB::commit();
            return response()->json(['message' => "Sukses"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal"], 400);
        }
    }
}
