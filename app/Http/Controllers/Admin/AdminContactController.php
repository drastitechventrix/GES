<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;

class AdminContactController extends Controller
{
  public function list(){
   $contacts = Contact::all();

        // Return the contacts as a JSON response
        return response()->json(['contacts' => $contacts], 200);
   }
}
