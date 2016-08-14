<?php

namespace App\Http\Controllers\Opwifi\Device;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Storage, Log, File;

use App\Models\OwDevFirmwares;

class FirmwareController extends Controller {

	protected $viewData = array(
	);

    public function __construct()
    {
    }

	private function checkFirmwares() {
		/* Remove unexist files */
		$fws = OwDevFirmwares::get();
		foreach ($fws as $fw) {
			if (!Storage::disk('uploads')->has($fw->filename)) {
				$fw->delete();
			}
		}
	}

	public function getIndex(Request $request) {
		return view("opwifi.device.firmware", $this->viewData);
	}

	public function getSelect(Request $request) {
		$limit = $request->input('limit');
		$offset = $request->input('offset');
		$order = $request->input('order');
		$sort = $request->input('sort');
		$tag_ids = $request->input('tag_ids');

		if ($offset) {
			$currentPage = floor($offset/$limit) + 1;
			Paginator::currentPageResolver(function() use ($currentPage) {
				return $currentPage;
			});
		}
		if ($limit) {
			if ($sort) {
				$fws = OwDevFirmwares::orderBy($sort, $order)->paginate($limit);
			} else {
				$fws = OwDevFirmwares::paginate($limit);
			}
		} else {
			$fws = OwDevFirmwares::get();
		}
		return response()->json($fws);
	}

	public function postUpload(Request $request) {
		if (!$request->hasFile('file')) {
	        return response()->json(['error' => 'No file in submit.' ]);
		}
        $file = $request->file('file');
        $name = $request->get('name');
        $id = $request->get('id');
        $allowed_extensions = ["bin", "img", "spkg"];
        if ($file->getClientOriginalExtension() &&
        		!in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
            return response()->json(['error' => 'You may only upload spkg, bin or img.']);
        }

        $extension = $file->getClientOriginalExtension();
        $fileName = str_random(16).'.'.$extension;
        $sha1 = sha1_file($file->getPathName());
        $ver = "";
        $hf = fopen ($file->getPathName(), "rb");
        $magic = fread($hf, 4);
        if (unpack("N", $magic)[1] == 0x5370eb67) {
        	fseek($hf, 0x88, SEEK_SET);
        	$info = fread($hf, 56);
        	$ver = unpack("Z*",$info)[1];
        }
        fclose($hf);

		$fw = new OwDevFirmwares();
		$fw->name = $name;
		$fw->org_filename = $file->getClientOriginalName();
		$fw->filename = $fileName;
		$fw->sha1 = $sha1;
		$fw->version = $ver;
		$fw->url = '/uploads/'.$fileName; //XXX: 需要更改。
		$fw->save();

        Storage::disk('uploads')->put($fileName,  File::get($file));

        $this->checkFirmwares();

        return response()->json(['success' => true, 'id' => $id]);
	}

	public function postDelete(Request $request) {
		if (!$request->isJson()) {
			return ;
		}
		$fws = $request->json()->all();
		foreach ($fws as $e) {
			if (!$e['id']) continue;
			$fw = OwDevFirmwares::where('id', $e['id'])->first();
			try{
				Storage::disk('uploads')->delete($fw->filename);
			} catch(\Exception $e) {}
			$fw->delete();
		}
		return response()->json(['success'=>true]);
	}

	public function postRename(Request $request) {
		$id = $request->get('id');
		if (!$id) {
			$id = $request->get('pk');
			$name = $request->get('value');
		} else {
			$name = $request->get('name');
		}
		$fw = OwDevFirmwares::where('id', $id)->first();
		if ($fw->count()) {
			$fw->update(['name'=>$name]);
			return response()->json(['success'=>true]);
		}
		return response()->json(['success'=>false]);
	}

}