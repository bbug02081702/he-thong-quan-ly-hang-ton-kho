<?php

namespace App\Http\Controllers;

use App\Exports\ExportSuppliers;
use App\Imports\SuppliersImport;
use App\User;
use Excel;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class UserController extends Controller {
	public function __construct() {
		$this->middleware('role:admin,staff');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		$users = User::all();
		return view('user.index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|unique:email',
		]);

		User::create($request->all());

		return response()->json([
			'success' => true,
			'message' => 'Đã tạo người dùng',
		]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$users = User::find($id);
		return $users;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$this->validate($request, [
			'name' => 'required|string|min:2',
			'email' => 'required|string|email|max:255|unique:suppliers',
		]);

		$users = User::findOrFail($id);

		$users->update($request->all());

		return response()->json([
			'success' => true,
			'message' => 'Đã cập nhật người dùng',
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		User::destroy($id);

		return response()->json([
			'success' => true,
			'message' => 'Đã xóa người dùng',
		]);
	}

	public function apiUsers() {
		$users = User::all();

		return Datatables::of($users)
			->addColumn('action', function ($users) {
				return '<a onclick="editForm(' . $users->id . ')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Sửa</a> ' .
				'<a onclick="deleteData(' . $users->id . ')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Xóa</a>';
			})
			->rawColumns(['action'])->make(true);
	}

	public function ImportExcel(Request $request) {
		//Validasi
		$this->validate($request, [
			'file' => 'required|mimes:xls,xlsx',
		]);

		if ($request->hasFile('file')) {
			//UPLOAD FILE
			$file = $request->file('file'); //GET FILE
			Excel::import(new SuppliersImport, $file); //IMPORT FILE
			return redirect()->back()->with(['success' => 'Tải lên nhà cung cấp dữ liệu tập tin!']);
		}

		return redirect()->back()->with(['error' => 'Vui lòng chọn tập tin trước!']);
	}

	public function exportSuppliersAll() {
		$suppliers = Supplier::all();
		$pdf = PDF::loadView('suppliers.SuppliersAllPDF', compact('suppliers'));
		return $pdf->download('suppliers.pdf');
	}

	public function exportExcel() {
		return (new ExportSuppliers)->download('suppliers.xlsx');
	}
}
