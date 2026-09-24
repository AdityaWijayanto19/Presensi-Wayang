<?php

namespace App\Http\Controllers;

class GuidelineController extends Controller
{
    public function index()
    {
        return view('karyawan.guideline.index');
    }

    public function wfh()
    {
        return view('karyawan.guideline.wfh');
    }

    public function izin()
    {
        return view('karyawan.guideline.izin');
    }

    public function lembur()
    {
        return view('karyawan.guideline.lembur');
    }

    public function cuti()
    {
        return view('karyawan.guideline.cuti');
    }
}
