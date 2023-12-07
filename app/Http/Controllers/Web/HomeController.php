<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class HomeController extends Controller {
  //
  public function index() {
      return "";
  }

  //stripe web hook needs this
  public function success() {
      return View("success");
  }

  //stripe web hook needs  this
  public function cancel() {
      return "No";
  }
}