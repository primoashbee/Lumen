<template>
<div class="row">
<div class="col-md-9">
    <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    <div class="card">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/clients">Client List</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Client</li>
          </ol>
        </nav>
          <div class="card-header">
            <h3 class="h3 ml-3">Profile</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="submit">
                
                <h3 class="px-3 my-3 text-2xl">Basic Information</h3>
                <div class="row px-3">
                <div class="form-group col-md-6 col-lg-3">
                    <label for="client_id" class="text-lg">Linked To</label>
                    <v2-select @officeSelected="assignOffice" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="hasError('office_id')">
                        {{ errors.office_id[0]}}
                    </div>
            
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    <label for="firstname" class="text-lg">First Name</label>
                    <input value="" type="text" id="firstname" v-model="fields.firstname" class="form-control"  v-bind:class="hasError('firstname') ? 'is-invalid' : ''" z>
                    <div class="invalid-feedback" v-if="hasError('firstname')">
                        {{ errors.firstname[0]}}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="middlename" class="title text-lg">Middle name</label>
                    <input value="" type="text" id="middlename" v-model="fields.middlename" class="form-control" v-bind:class="hasError('middlename') ? 'is-invalid' : ''">
                    <div class="invalid-feedback" v-if="hasError('middlename')">
                        {{ errors.middlename[0]}}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="lastname" class="text-lg">Last name</label>
                    <input value="" id="lastname" type="text" v-model="fields.lastname" class="form-control" v-bind:class="hasError('lastname') ? 'is-invalid' : ''" z>
                    <div class="invalid-feedback" v-if="hasError('lastname')">
                        {{ errors.lastname[0]}}
                    </div>
                </div>
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-2">
                        <label for="suffix">Suffix </label>
                        <input value="" type="text" v-model="fields.suffix" id="suffix" class="form-control" v-bind:class="hasError('suffix') ? 'is-invalid' : ''" >
                        <div class="invalid-feedback" v-if="hasError('suffix')">
                            {{ errors.suffix[0]}}
                        </div>
                    </div>
            
                    <div class="form-group col-md-3">
                        <label for="nickname">Nickname</label>
                        <input value="" type="text" v-model="fields.nickname" id="nickname" class="form-control" v-bind:class="hasError('nickname') ? 'is-invalid' : ''">
                        <div class="invalid-feedback" v-if="hasError('nickname')">
                            {{ errors.nickname[0]}}
                        </div>
                    </div>
            
                    <div class="form-group px-3 col-md-3">
                        <label for="gender">Gender</label>
                        <div class="select" v-bind:class="hasError('gender') ? 'is-invalid' : ''">
                            <select v-model="fields.gender" id="gender" class="form-control" v-bind:class="hasError('gender') ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="MALE">MALE</option>
                                <option value="FEMALE">FEMALE</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="hasError('gender')">
                            {{ errors.gender[0]}}
                        </div>        
                        
                    </div>
            
                    <div class="form-group col-md-4 px-3">
                        <label for="civil_status">Marital Status</label>
                        <div class="select" v-bind:class="hasError('civil_status') ? 'is-invalid' : ''">
                            <select v-model="fields.civil_status" id="civil_status" class="form-control" v-bind:class="hasError('civil_status') ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="SINGLE">SINGLE</option>
                                <option value="MARRIED">MARRIED</option>
                                <option value="DIVORCED">DIVORCED</option>
                                <option value="WIDOWED">WIDOWED</option>
                                <option value="SEPARATED">SEPARATED</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="hasError('civil_status')">
                            {{ errors.civil_status[0]}}
                        </div> 
                    </div>
            
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-6">
                        <label id="fb_account">Facebook Account</label>
                        <input value="" type="text" v-model="fields.fb_account" id="fb_account" class="form-control" v-bind:class="hasError('facebook') ? 'is-invalid' : ''" z>
            
                        <div class="invalid-feedback" v-if="hasError('facebook')">
                            {{ errors.facebook[0]}}
                        </div>    
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label id="contact_number">Phone Number</label>
                        <input value="" type="text" v-mask="masks.phone" placeholder="09XXXXXXXXX" v-model="fields.contact_number" id="contact_number" class="form-control" v-bind:class="hasError('contact_number') ? 'is-invalid' : ''" z>
                    
                        <div class="invalid-feedback" v-if="hasError('contact_number')">
                            {{ errors.contact_number[0]}}
                        </div>   
                    </div> 
                </div>
                <div class="row px-3">
            
                    <div class="form-group col-md-3 px-3">
                        <label for="birthday">Birthday</label>
                        
                        <input type="date" v-model="fields.birthday" class="form-control"  v-bind:class="hasError('birthday') ? 'is-invalid' : ''" >
                        <div class="invalid-feedback" v-if="hasError('birthday')">
                            {{ errors.birthday[0]}}
                        </div>  
                    </div>
            
                    <div class="form-group col-md-4 px-3">
                        <label for="birthplace">Birthplace</label>
                        <input value="" id="birthplace" type="text" class="form-control" v-model="fields.birthplace" v-bind:class="hasError('birthplace') ? 'is-invalid' : ''" z autocomplete="birthplace">
                        <div class="invalid-feedback" v-if="hasError('birthplace')">
                            {{ errors.birthplace[0]}}
                        </div>  
                    </div>
            
            
                    <div class="form-group col-md-5 px-3">
                        <label for="education">Educational Attainment</label>
                        <div class="select" v-bind:class="hasError('education') ? 'is-invalid' : ''">
                            <select v-model="fields.education" id="education" class="form-control" v-bind:class="hasError('education') ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="COLLEGE">COLLEGE</option>
                                <option value="VOCATIONAL">VOCATIONAL</option>
                                <option value="HIGH SCHOOL">HIGH SCHOOL</option>
                                <option value="ELEMENTARY">ELEMENTARY</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="hasError('education')">
                            {{ errors.education[0]}}
                        </div>  
                    </div>
                </div>
                <hr>
                <h3 class="px-3 my-4 text-2xl title">Address Information</h3>
                <div class="row px-3">
                    <div class="form-group px-3 col-md-9">
                        <label for="street_address">Street Address</label>
                        <input value="" type="text" v-model="fields.street_address" id="street_address" class="form-control" v-bind:class="hasError('street_address') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('street_address')">
                            {{ errors.street_address[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-3 px-2">
                        <label for="barangay_address">Barangay</label>
                        <input value="" type="text" v-model="fields.barangay_address" id="barangay_address" class="form-control" v-bind:class="hasError('street_address') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('street_address')">
                            {{ errors.barangay_address[0]}}
                        </div>      
                    </div>
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-4">
                        <label for="city_address">City</label>
                        <input value="" type="text" v-model="fields.city_address" id="city_address" class="form-control" v-bind:class="hasError('city_address') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('city_address')">
                            {{ errors.city_address[0]}}
                        </div>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="province_address">Province</label>
                        <input value="" type="text" v-model="fields.province_address" id="province_address" class="form-control" v-bind:class="hasError('province_address') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('province_address')">
                            {{ errors.province_address[0]}}
                        </div>
                    </div>
            
            
                    <div class="form-group col-md-4 px-2">
                        <label for="zipcode">Postal/Zip code</label>
                        <input value="" type="text" v-mask="masks.zipcode" v-model="fields.zipcode" id="zipcode" class="form-control" v-bind:class="hasError('zipcode') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('zipcode')">
                            {{ errors.zipcode[0]}}
                        </div>
                    </div>
                </div>
                
                <h3 class="px-3 my-3 text-2xl title">Business Information</h3>	
                
                <div class="col-12"> 
                    <div class=" text-2xl business" v-for="(item, key) in fields.businesses" :key="item.id">
                        <hr>
                        <h1 class="title"> Business #: {{key + 1}} </h1>
                        <button type="button" class="btn btn-sm btn-danger float-right" @click="removeBusiness(key)">X</button>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label :for="'business_address_'+key">Full Business Address </label>
                                <input :id="'business_address_'+key" type="text" v-model="fields.businesses[key].business_address" class="form-control" v-bind:class="hasBusinessesError(key,'business_address') ? 'is-invalid' : ''" z>
                                <div class="invalid-feedback" v-if="hasBusinessesError(key,'business_address') ">
                                    {{ businessesErrorMsg(key,'business_address') }}
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label :for="'service_type_'+key">Service Type</label>
                                <select :id="'service_type_'+key" class="form-control" v-bind:class="hasBusinessesError(key,'service_type') ? 'is-invalid' : ''"  v-model="fields.businesses[key].service_type" >
                                    <option value=""> CHOOSE AN OPTION</option> 
                                    <option value="AGRICULTURE"> AGRICULTURE </option>
                                    <option value="TRADING/MERCHANDISING"> TRADING/MERCHANDISING </option>
                                    <option value="MANUFACTURING"> MANUFACTURING </option>
                                    <option value="SERVICES"> SERVICES </option>
                                    <option value="OTHERS"> OTHERS </option>
                                </select>
                                <div class="invalid-feedback" v-if="hasBusinessesError(key,'service_type') ">
                                    {{ businessesErrorMsg(key,'service_type') }}
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label :for="'monthly_gross_income_'+key">Monthly Gross Income</label>
                                <input type="number" class="form-control" :id="'monthly_gross_income_'+key" min="0" v-model="fields.businesses[key].monthly_gross_income" v-bind:class="hasBusinessesError(key,'monthly_gross_income') ? 'is-invalid' : ''" />
                                <div class="invalid-feedback" v-if="hasBusinessesError(key,'monthly_gross_income') ">
                                    {{ businessesErrorMsg(key,'monthly_gross_income') }}
                                </div>
                            </div>
                            <div class="form-group col-4">
                                <label :for="'monthly_operating_expense_'+key">Monthly Operating Expense</label>
                                <input type="number" class="form-control" :id="'monthly_operating_expense_'+key" min="0" v-model="fields.businesses[key].monthly_operating_expense" v-bind:class="hasBusinessesError(key,'monthly_operating_expense') ? 'is-invalid' : ''" />
                                <div class="invalid-feedback" v-if="hasBusinessesError(key,'monthly_operating_expense') ">
                                    {{ businessesErrorMsg(key,'monthly_operating_expense') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-primary " @click="addNewBusiness">Add New Business</button>
                    </div>

                 
                </div>
                <hr>
                <h3 class="px-3 my-3 text-2xl title">Family Information</h3>	
                <div class="row px-3">
                    <div class="form-group px-3 col-md-3">
                        <label for="number_of_dependents">No. of dependents</label>
                        <input value="" type="text" v-mask="masks.dependents" v-model="fields.number_of_dependents" id="number_of_dependents" class="form-control" v-bind:class="hasError('number_of_dependents') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('number_of_dependents')">
                            {{ errors.number_of_dependents[0]}}
                        </div>        
                        
                    </div>
                    <div class="form-group px-3 col-md-3">
                        <label for="household_size">Household Size</label>
                        <input type="text" v-mask="masks.household_size" v-model="fields.household_size" id="household_size" class="form-control" v-bind:class="hasError('household_size') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('household_size')">
                            {{ errors.household_size[0]}}
                        </div>
                    </div>
                    <div class="form-group px-3 col-md-3">
                        <label for="years_of_stay_on_house">Years of Residency</label>
                        <input type="text" v-mask="masks.years_of_stay_on_house" v-model="fields.years_of_stay_on_house" id="years_of_stay_on_house" class="form-control" v-bind:class="hasError('years_of_stay_on_house') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('years_of_stay_on_house')">
                            {{ errors.years_of_stay_on_house[0]}}
                        </div>
                    </div>
            
                    <div class="form-group px-3 col-md-3 col-md-offset-2">
                        <label for="house_type">House Type</label>
                        <select v-model="fields.house_type" id="house_type" class="form-control" v-bind:class="hasError('house_type') ? 'is-invalid' : ''">
                            <option value="">CHOOSE AN OPTION</option>
                            <option value="OWNED">OWNED</option>
                            <option value="RENTED">RENTED</option>
                        </select>
                        <div class="invalid-feedback" v-if="hasError('house_type')">
                            {{ errors.house_type[0]}}
                        </div>
                    </div>
            
                </div>
            

                <div class="row px-3">
                    <div class="form-group col-md-6">
                        <label for="spouse_name">Spouse Name</label>
                        <input value="" type="text" v-model="fields.spouse_name" class="form-control" id="spouse_name" v-bind:class="hasError('spouse_name') ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="hasError('spouse_name')">
                            {{ errors.spouse_name[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="spouse_contact_number">Spouse Contact Number</label>
                        <input value="" type="text" v-mask="masks.phone" v-model="fields.spouse_contact_number" class="form-control" id="spouse_contact_number" v-bind:class="hasError('spouse_contact_number') ? 'is-invalid' : ''" placeholder="09XXXXXXXXX" z>
                        <div class="invalid-feedback" v-if="hasError('spouse_contact_number')">
                            {{ errors.spouse_contact_number[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4 col-md-offset-8">
                        <label for="spouse_birthday">Spouse Birthday</label>
                        <input type="date" v-model="fields.spouse_birthday" class="form-control"  v-bind:class="hasError('spouse_birthday') ? 'is-invalid' : ''" >
                        
                        <div class="invalid-feedback" v-if="hasError('spouse_birthday')">
                            {{ errors.spouse_birthday[0]}}
                        </div>
                    </div>
                </div>

                <hr>
                <h3 class="px-3 my-4 text-2xl title">Statutories</h3>
            
                <div class="row px-3">
                    <div class="form-group col-md-4">
                        <label>TIN</label>
                        <input value="" type="text" v-mask="masks.tin" v-model="fields.tin" id="tin" v-bind:class="hasError('tin') ? 'is-invalid' : ''"  class="form-control">
                        <div class="invalid-feedback" v-if="hasError('tin')">
                            {{ errors.tin[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>SSS</label>
                        <input value="" type="text" v-mask="masks.sss" v-model="fields.sss" id="sss" v-bind:class="hasError('sss')  ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="hasError('sss') ">
                            {{ errors.sss[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>UMID</label>
                        <input value="" type="text" v-mask="masks.umid" v-model="fields.umid" id="umid" v-bind:class="hasError('umid')  ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="hasError('umid') ">
                            {{ errors.umid[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-6 col-md-offset-6">
                        <label>Mothers Maiden Name</label>
                        <input value="" type="text" v-model="fields.mother_maiden_name" v-bind:class="hasError('tin')  ? 'is-invalid' : ''" id="mother_maiden_name" class="form-control" z>
                        <div class="invalid-feedback" v-if="hasError('tin') ">
                            {{ errors.mother_maiden_name[0]}}
                        </div>
                    </div>
                </div>
            

                <hr>
                <h3 class="px-3 my-4 text-2xl title">Household Income</h3>

                <div class="row px-3">
                    <h3 class="col-md-6 px-3 my-2 text-2xl title">Personal</h3>
                    <h3 class="col-md-6 my-2 px-3 text-2xl title">Spouse</h3>
                    <div class="form-group col-md-3">
                        <div class="p0 form-check">
                            <label class="form-check-label" for="is_self_employed">
                                <input class="form-check-input cb-type" :disabled = "fields.is_employed" :class = "{disabled:fields.is_employed}" v-model ="fields.is_self_employed" id="is_self_employed" type="checkbox" name="is_self_employed" value="">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="is_self_employed">Self-Employed</label>
                            </label>
                        </div>
                        <div class="hi form-group p0 my-2" data-attribute="is_self_employed" :class="{active:fields.is_self_employed}">
                            <label for="sevice_type">Service Type</label>
                            <div class="select">
                                <select v-model="fields.service_type" id="service_type">
                                    <option value=""> CHOOSE AN OPTION</option> 
                                    <option value="AGRICULTURE"> AGRICULTURE </option>
                                    <option value="TRADING/MERCHANDISING"> TRADING/MERCHANDISING </option>
                                    <option value="MANUFACTURING"> MANUFACTURING </option>
                                    <option value="SERVICE"> SERVICE </option>
                                    <option value="OTHERS"> OTHERS </option>
                                </select>
                            </div>
                        </div>
                        <div class="hi form-group p0 my-2" data-attribute="is_self_employed" :class="{active:fields.is_self_employed}">
                            <label for="service_type_monthly_gross_income">Monthly Income</label>
                            <input type="number" step ="0.01" id="service_type_monthly_gross_income" v-model="fields.service_type_monthly_gross_income" class="form-control" value="service_type_monthly_gross_income">
                        </div>
                    </div>
            
                    <div class="form-group col-md-3">
                        <div class="p0 form-check">
                            <label class="form-check-label" for="is_employed">
                                <input class="form-check-input cb-type" id="is_employed" :disabled = "fields.is_self_employed" :class="{disabled:fields.is_self_employed}" type="checkbox" v-model="fields.is_employed">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="is_employed">Working on Company</label>
                            </label>
                        </div>
                        <div class="hi my-2" data-attribute="is_employed" :class="{active:fields.is_employed}">
                            <div class="row">
                                <div class="form-group w-100">
                                    <label for="employed_position">Position</label>
                                    <input value="" type="text" v-model="fields.employed_position" class="form-control" id="employed_position">
                                </div>
                                <div class="form-group w-100">
                                    <label for="employed_company_name">Company Name</label>
                                    <input value="" type="text" v-model="fields.employed_company_name" class="form-control" id="employed_company_name">
                                </div>
                                <div class="form-group w-100">
                                    <label for="employed_monthly_gross_income">Monthly Gross income</label>
                                    <input value="" type="number" step ="0.01"  v-model="fields.employed_monthly_gross_income" class="form-control" id="employed_monthly_gross_income">
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <div class="form-group col-md-3">
                        <div class="p0 form-check">
                            <label class="form-check-label" for="spouse_is_self_employed">
                                <input class="form-check-input cb-type" :disabled="fields.spouse_is_employed" id="spouse_is_self_employed" :class="{disabled:fields.spouse_is_employed}" type="checkbox" v-model="fields.spouse_is_self_employed" value="">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="spouse_is_self_employed">Spouse Self-Employed</label>
                            </label>
                        </div>
                        <div class="hi form-group col-md-12 p0 my-2" :class="{active:fields.spouse_is_self_employed}" data-attribute="spouse_is_self_employed">
                            <label for="spouse_service_type">Spouse Service Type</label>
                            <select v-model="fields.spouse_service_type" id="spouse_service_type" class="form-control">
                                <option value=""> CHOOSE AN OPTION</option> 
                                <option value="AGRICULTURE"> AGRICULTURE </option>
                                <option value="TRADING/MERCHANDISING"> TRADING/MERCHANDISING </option>
                                <option value="MANUFACTURING"> MANUFACTURING </option>
                                <option value="SERVICE"> SERVICE </option>
                                <option value="OTHERS"> OTHERS </option>
                            </select>
                        </div>
                        <div class="hi form-group p0 my-2" :class="{active:fields.spouse_is_self_employed}" data-attribute="spouse_is_self_employed" >
                            <label for="spouse_service_type_monthly_gross_income">Monthly Income</label> 
                            <input type="number" step ="0.01" id="spouse_service_type_monthly_gross_income" v-model="fields.spouse_service_type_monthly_gross_income" value="" class="form-control">
                        </div>
                    </div>
            
                    <div class="form-group col-md-3">
                        <div class="p0 form-check">
                            <label class="form-check-label" for="spouse_is_employed">
                                <input class="form-check-input cb-type" id="spouse_is_employed" type="checkbox" v-model="fields.spouse_is_employed" :disabled="fields.spouse_is_self_employed" :class="{disabled:fields.spouse_is_self_employed}">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="spouse_is_employed">Spouse Working on Company</label>
                            </label>
                        </div>
                        <div class="hi my-2" data-attribute="spouse_is_employed" :class="{active:fields.spouse_is_employed}">
                            <div class="row">
                                <div class="form-group w-100">
                                    <label for="spouse_employed_position">Position</label>
                                    <input value="" type="text" v-model="fields.spouse_employed_position" class="form-control" id="spouse_employed_position">
                                </div>
                                <div class="form-group w-100">
                                    <label for="spouse_company_name">Spouse Company Name</label>
                                    <input value="" type="text" v-model="fields.spouse_employed_company_name" class="form-control" id="spouse_employed_company_name">
                                </div>
                                <div class="form-group w-100">
                                    <label for="spouse_employed_monthly_gross_income">Spouse Monthly Gross income</label>
                                    <input value="" type="number" v-model="fields.spouse_employed_monthly_gross_income" class="form-control" id="spouse_employed_monthly_gross_income">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <h3 class="px-3 my-4 text-2xl title">Other Income</h3>	
                <div class="row px-3">
                    <div class="form-group col-md-3">
                        <div class="form-check p0">
                            <label class="form-check-label" for="has_remittance">
                                <input class="form-check-input cb-type" id="has_remittance" type="checkbox" v-model="fields.has_remittance">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="has_remittance">Remittance</label>
                            </label>
                        </div>
                        <div class="hi form-group col-md-12 p0 my-2" data-attribute="has_remittance" :class="{active:fields.has_remittance}"  >
                            <label for="remittance_amount">Remittance Amount</label>
                            <input value="" type="number" v-model="fields.remittance_amount" class="form-control" id="remittance_amount">
                        </div>
                    </div>
            
                    <div class="form-group col-md-3">
                        <div class="p0 form-check">
                            <label class="form-check-label" for="has_pension">
                                <input class="form-check-input cb-type" id="has_pension" type="checkbox" v-model="fields.has_pension">
                                <span class="form-check-sign">
                                <span class="check"></span>
                                </span>
                                <label for="has_pension">Pension</label>
                            </label>
                        </div>
                        <div class="hi form-group col-md-12 p0 my-2" data-attribute="has_pension" :class="{active:fields.has_pension}" >
                            <label for="pension_amount">Pension Amoount</label>
                            <input value="" type="number" v-model="fields.pension_amount" class="form-control" id="pension_amount">
                        </div>
                    </div>
                </div>
            <hr>
            <h3 class="px-3 my-3 text-2xl title"> Cash Flow - Monthly </h3>
                <div class="row px-3 my-3">
                    <div class="form-group col-4">
                        <label class="">Total household income</label>
                        <input type="text" class="form-control" v-bind:class="hasError('total_household_gross_income')  ? 'is-invalid' : ''"  readonly="true" :value="total_household_gross_income">
                        <div class="invalid-feedback" v-if="hasError('total_household_gross_income') ">
                            {{ errors.total_household_gross_income[0]}}
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <label class="">Total household expense</label>
                        <input type="text" v-model="fields.total_household_expense" class="form-control" v-bind:class="hasError('total_household_expense')  ? 'is-invalid' : ''" >
                        <div class="invalid-feedback" v-if="hasError('total_household_expense') ">
                            {{ errors.total_household_expense[0]}}
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <label class="">Total household net income </label>
                        <input type="text" readonly :value="total_household_net_income" class="form-control" v-bind:class="hasError('total_household_net_income')  ? 'is-invalid' : ''" >
                        <div class="invalid-feedback" v-if="hasError('total_household_net_income') ">
                            {{ errors.total_household_net_income[0]}}
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <label for="total_business_income">Total Business Income</label>
                        <input type="text" :value="total_businesses_gross_income" class="form-control" v-bind:class="hasError('total_businesses_gross_income')  ? 'is-invalid' : ''" readonly>
                        <div class="invalid-feedback" v-if="hasError('total_businesses_gross_income') ">
                            {{ errors.total_businesses_gross_income[0]}}
                        </div>
                    </div> 
                    <div class="form-group col-4">
                        <label for="total_business_income">Total Business Expense</label>
                        <input type="text" :value="total_businesses_expense" class="form-control" v-bind:class="hasError('total_businesses_expense')  ? 'is-invalid' : ''" readonly>
                        <div class="invalid-feedback" v-if="hasError('total_businesses_expense') ">
                            {{ errors.total_businesses_expense[0]}}
                        </div>
                    </div>
                    <div class="form-group col-4">
                        <label class="">Total Business Net income </label>
                        <input type="text" :value="total_businesses_net_income" class="form-control" v-bind:class="hasError('total_businesses_net_income')  ? 'is-invalid' : ''" readonly>
                        <div class="invalid-feedback" v-if="hasError('total_businesses_net_income') ">
                            {{ errors.total_businesses_net_income[0]}}
                        </div>
                    </div>
                </div>
            
                <h4 class="my-2 px-3 text-2xl title">Notes</h4>
                <div class="row px-3">
                    <div class="form-group col-md-12">
                        <textarea value="notes" rows="3" cols="40" v-model="fields.notes" class="form-control"></textarea>
                    </div>
                    
                </div>
                 <button type="submit" class="btn btn-primary"> Submit </button>
                </form>
          </div>
    </div>
</div>
  
  <div class="col-md-3">
     <div class="card">
         <div class="card-header">
             <h4 class="text-center h4">Attachment</h4>
         </div>

        <div class="card-body">
            <div  class="file-input-profile d-block text-center position-relative mb-4">
                <img :src="fields.profile_picture_path_preview" class="img-responsive" v-bind:class="hasError('profile_picture_path')  ? 'is-invalid' : ''"  style=""> 
                <div class="file-input text-center">
                    <span class="position-relative btn btn-rose btn-round btn-file">
                        <span class="fileinput-new">Image</span>
                        <input value="" type="file" class="attachment" name="profile_picture_path" @change="onFileSelected($event,'profile_picture_path')">
                    </span>
                </div>
                <div class="invalid-feedback" v-if="hasError('profile_picture_path') ">
                    {{ errors.profile_picture_path[0]}}
                </div>  
            </div>
            <div  class="file-input-signature d-block text-center position-relative">
                <img :src="fields.signature_path_preview" class="img-thumbnail" v-bind:class="hasError('signature_path')  ? 'is-invalid' : ''" alt="Cinque Terre" > 
                <div class="file-input text-center">
                    <span class="position-relative btn btn-rose btn-round btn-file">
                    <span class="fileinput-new">Signature</span>
                    <input value="signature_path" type="file" class="attachment" name="signature_path" @change="onFileSelected($event,'signature_path')">
                    </span>
                </div>
                <div class="invalid-feedback" v-if="hasError('signature_path')  ">
                    {{ errors.signature_path[0]}}
                </div>  
            </div>
         </div>
     </div>
     <div class="card" v-show="hasErrors">
         
                <ul class="list-group">
                    <li class="list-group-item list-group-item-danger">
                       <p style="font-size:1.5em; text-align:center;font-weight:bold"> List of Errors </p>
                    </li>
                    <li class="list-group-item list-group-item-danger" v-for="error in errors" :key="error.id">
                        <p v-for="list in error" :key="list.id">{{ list }}</p>
                    </li>
                </ul>
     </div>
     
 </div> 
</div>       
</template>

<script>

import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Loading from 'vue-loading-overlay';
import { serialize } from 'object-to-formdata';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        SelectComponentV2,
        Loading 
    },
    data(){
        return {
            formD : null,
            masks: {
                tin:'###-###-###-###',
                sss:'##-#######-#',
                umid:'####-#######-#',
                phone: '###########',
                zipcode: '####',
                dependents: '##',
                household_size:'##',
                years_of_stay_on_house:'###'
            },
            isLoading:false,
            fields: {
                'businesses': [],
                'total_expense':0,
                'office_id':"",
                'firstname':"",
                'middlename':"",
                'lastname':"",
                'suffix':"",
                'nickname':"",
                'gender':"",
                'civil_status':"",
                'fb_account':"",
                'contact_number':null,
                'birthday':"",
                'birthplace':"",
                'education':"",
                'street_address':"",
                'barangay_address':"",
                'city_address':"",
                'province_address':"",
                'zipcode':"",
                'business_address':"",
                'number_of_dependents':"",
                'household_size':null,
                'years_of_stay_on_house':null,
                'house_type':"",
                'spouse_name':"",
                'spouse_contact_number':"",
                'spouse_birthday':"",
                'tin':"",
                'sss':"",
                'umid':"",
                'mother_maiden_name':"",

                'is_self_employed':false,
                'service_type':"",
                'service_type_monthly_gross_income':0,

                'is_employed':false,
                'employed_position':"",
                'employed_company_name':"",
                'employed_monthly_gross_income':0,

                'spouse_is_self_employed':false,
                'spouse_service_type':"",
                'spouse_service_type_monthly_gross_income':0,

                'spouse_is_employed':false,
                'spouse_employed_position':"",
                'spouse_employed_company_name':"",
                'spouse_employed_monthly_gross_income':0,

                'has_remittance':false,
                'remittance_amount':0,

                'has_pension':false,
                'pension_amount':0,

                'total_household_expense':0,
                'notes':"",

                'profile_picture_path_preview':location.origin + '/assets/img/2x2.jpg',
                'signature_path_preview': location.origin + '/assets/img/signature.png',


                'profile_picture_path': null,
                'signature_path': null,
            },
            errors: {

            },
            
        }
    },
    computed:{
        total_businesses_gross_income(){
            if(this.fields.businesses.length > 0){
                var total = 0;
                this.fields.businesses.map(x=>{
                    total = total + parseFloat(x.monthly_gross_income)
                })
                
                return total
            }
            return 0;
        },
        total_businesses_expense(){
            if(this.fields.businesses.length > 0){
                var total = 0;
                this.fields.businesses.map(x=>{
                    total = total + parseFloat(x.monthly_operating_expense)
                })
                
                return total
            }
            return 0;
        },
        total_businesses_net_income(){
            let total = this.total_businesses_gross_income - this.total_businesses_expense;
            
            if(isNaN(total)){
                return 0;
            }
            return total;
        },
        total_household_net_income(){
            let total =  parseFloat(this.total_household_gross_income) - parseFloat(this.fields.total_household_expense)
               if(isNaN(total)){
                return 0;
            }
            return total;
        },
        total_household_gross_income(){
            var total = parseFloat(this.fields.service_type_monthly_gross_income) + parseFloat(this.fields.employed_monthly_gross_income) +  parseFloat(this.fields.spouse_service_type_monthly_gross_income) +parseFloat(this.fields.spouse_employed_monthly_gross_income) + parseFloat(this.fields.remittance_amount) + parseFloat(this.fields.pension_amount) 
            if(isNaN(total)){
                return 'Use positive integers for amounts only';
            }
            return total;
        },
        hasErrors(){
            return Object.keys(this.errors).length > 0;
        },
        
        duplicateClient(){
            return this.errors.hasOwnProperty('client')
        },
        // isEmployedDisabled(){
        //     return this.fields.is_self_employed
        // },
        // isSelfEmployedDisabled(){
        //     return this.fields.is_employed
        // },
        // isSpouseSelfEmployedDisabled(){
        //     return this.fields.spouse_is_employed
        // },
        // isSpouseEmployedDisabled(){
        //     return this.fields.spouse_is_self_employed
        // }

    },

    created(){
       
    },
    methods: {
        removeBusiness(key){
            this.fields.businesses.pop(key)
        },
        addNewBusiness(){
            this.fields.businesses.push({
                business_address:null,
                service_type:null,
                monthly_gross_income: null,
                monthly_operating_expense: null
            })
        },
        hasError(field){
            return this.errors.hasOwnProperty(field)
        },
        hasBusinessesError(index,field){
            if(this.errors['businesses.'+index+'.'+field] != undefined){
                return true;
            }
            return false;
        },
        businessesErrorMsg(index,field){
            if(this.hasBusinessesError(index,field)){
                console.log('businesses.'+index+'.'+field)
                return this.errors['businesses.'+index+'.'+field][0];
            }
            
        },
        removeImage: function (e) {
            this.image = '';
        },
        onChildClick(value){
            alert(this.fromChild)
        },
        assignOffice(value){
            this.fields.office_id = value['id']
        },
        getDate(value, field){
           this.fields[field] = value
        },
        clientExists(){
            return Object.keys(this.errors.client).length > 0
        },

        onFileSelected(e,id) {
              const file = e.target.files[0]
              if(id == "profile_picture_path"){
                  this.fields.profile_picture_path_preview = URL.createObjectURL(file)
              }
              if(id == "signature_path"){
                  this.fields.signature_path_preview = URL.createObjectURL(file)
              }
              console.log(file)
              this.fields[id] = file;

        },
        submit(){
            var vm = this
            this.errors = {}
            this.fields['total_household_gross_income'] = this.total_household_gross_income
            this.fields['total_household_net_income'] = this.total_household_net_income
            this.fields['total_businesses_gross_income'] = this.total_businesses_gross_income
            this.fields['total_businesses_expense'] = this.total_businesses_expense
            this.fields['total_businesses_net_income'] = this.total_businesses_net_income

            // const options = {

            //     indices: true,

            //     /**
            //      * treat null values like undefined values and ignore them
            //      * defaults to false
            //      */
            //     nullsAsUndefineds: false,

            //     /**
            //      * convert true or false to 1 or 0 respectively
            //      * defaults to false
            //      */
            //     booleansAsIntegers: true,

            //     /**
            //      * store arrays even if they're empty
            //      * defaults to false
            //      */
            //     allowEmptyArrays: false,
            // };
            // formData = serialize(this.fields,options)
            // console.log(this.profile_picture_path)
            // formData.append('profile_picture_path',this.fields.profile_picture_path)
            // formData.append('signature_path',this.fields.signature_path)
            // if(this.profile_picture_path ==location.origin + '/assets/img/anime3.png'){
            //     console.log(this.profile_picture_path)
            //     formData.append('profile_picture_path',this.fields.profile_picture_path)
            // }
            // if(this.signature_path==location.origin + '/assets/img/signature.png'){
            //     formData.append('signature_path',this.fields.signature_path)
            // }

            
            
            axios.post('/create/client', this.fields)
                .then(res=>{
                    this.isLoading = false
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK'
                    })
                    .then(res=>{
                        location.reload();
                    })
                })
                .catch(error=>{
                    this.isLoading = false
                    this.errors = error.response.data.errors || {}
                    if(this.duplicateClient){
                        Swal.fire({
                            icon: 'error',
                            title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">OOPPPSSSSS!</span>',
                            text: this.errors.client.msg,
                            footer: '<a href="#">Client ['+this.errors.client.client_id+'] already existing at: ' +this.errors.client.exists_at +'</a>'
                        })
                    }
                })
        },
    },
    watch : {
        'fields.is_self_employed' : function (newVal,oldVal){
           if(!newVal){
               console.log('unclicked');
               this.fields.service_type = ""
               this.fields.service_type_monthly_gross_income = 0
               return;
           }
        },
        'fields.is_employed' : function (newVal,oldVal){
           if(!newVal){
               this.fields.employed_position = ""
               this.fields.employed_company_name =  ""
               this.fields.employed_monthly_gross_income = 0
               return;
           }
        },
        'fields.spouse_is_self_employed' : function (newVal,oldVal){
           if(!newVal){
               this.fields.spouse_service_type = ""
               this.fields.spouse_service_type_monthly_gross_income = 0
               return;
           }
        },
        'fields.spouse_is_employed' : function (newVal,oldVal){
           if(!newVal){
               this.fields.spouse_employed_position = ""
               this.fields.spouse_employed_company_name = ""
               this.fields.spouse_employed_monthly_gross_income = 0
               return;
           }
        },
        'fields.has_remittance' : function (newVal,oldVal){
           if(!newVal){
               this.fields.remittance_amount = 0;
               return;
           }
        },
        'fields.has_pension' : function (newVal,oldVal){
           if(!newVal){
               this.fields.pension_amount = 0;
               return;
           }
        }
    }
}
</script>