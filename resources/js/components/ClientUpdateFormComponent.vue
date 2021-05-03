<template>
<div class="row">
<div class="col-md-9">
    <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    <div class="card">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/clients">Client List</a></li>
            <li class="breadcrumb-item"><a :href="toClient()">Profile</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Client</li>
          </ol>
        </nav>
          <div class="card-header">
            <h3 class="h3 ml-3">Profile</h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="submit">
                <h3 class="text-2xl my-3 px-3 title">Basic Information</h3>
                <div class="row px-3">
                <div class="form-group col-md-6 col-lg-3">
                    <label for="client_id">Linked To</label>
                    <v2-select @officeSelected="assignOffice" :default_value ="fields.office_id" v-bind:class="officeHasError ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="officeHasError">
                        {{ errors.office_id[0]}}
                    </div>
            
                </div>
                <div class="form-group col-md-3 col-lg-3">
                    <label for="firstname">First Name</label>
                    <input value="" type="text" id="firstname" v-model="fields.firstname" class="form-control"  v-bind:class="firstNameHasError ? 'is-invalid' : ''" z>
                    <div class="invalid-feedback" v-if="firstNameHasError">
                        {{ errors.firstname[0]}}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="middlename">Middle name</label>
                    <input value="" type="text" id="middlename" v-model="fields.middlename" class="form-control" v-bind:class="middleNameHasError ? 'is-invalid' : ''">
                    <div class="invalid-feedback" v-if="middleNameHasError">
                        {{ errors.middlename[0]}}
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label for="lastname">Last name</label>
                    <input value="" id="lastname" type="text" v-model="fields.lastname" class="form-control" v-bind:class="lastNameHasError ? 'is-invalid' : ''" z>
                    <div class="invalid-feedback" v-if="lastNameHasError">
                        {{ errors.lastname[0]}}
                    </div>
                </div>
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-2">
                        <label for="suffix">Suffix </label>
                        <input value="" type="text" v-model="fields.suffix" id="suffix" class="form-control" v-bind:class="suffixNameHasError ? 'is-invalid' : ''" >
                    </div>
            
                    <div class="form-group col-md-3">
                        <label for="nickname">Nickname</label>
                        <input value="" type="text" v-model="fields.nickname" id="nickname" class="form-control" v-bind:class="nickNameHasError ? 'is-invalid' : ''">
                    </div>
            
                    <div class="form-group px-3 col-md-3">
                        <label for="gender">Gender</label>
                        <div class="select" v-bind:class="genderHasError ? 'is-invalid' : ''">
                            <select v-model="fields.gender" id="gender" class="form-control" v-bind:class="genderHasError ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="MALE">MALE</option>
                                <option value="FEMALE">FEMALE</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="genderHasError">
                            {{ errors.gender[0]}}
                        </div>        
                        
                    </div>
            
                    <div class="form-group col-md-4 px-3">
                        <label for="civil_status">Marital Status</label>
                        <div class="select" v-bind:class="civilStatusHasError ? 'is-invalid' : ''">
                            <select v-model="fields.civil_status" id="civil_status" class="form-control" v-bind:class="civilStatusHasError ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="SINGLE">SINGLE</option>
                                <option value="MARRIED">MARRIED</option>
                                <option value="DIVORCED">DIVORCED</option>
                                <option value="WIDOWED">WIDOWED</option>
                                <option value="SEPARATED">SEPARATED</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="civilStatusHasError">
                            {{ errors.civil_status[0]}}
                        </div> 
                    </div>
            
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-6">
                        <label id="fb_account">Facebook Account</label>
                        <input value="" type="text" v-model="fields.fb_account" id="fb_account" class="form-control" v-bind:class="facebookHasError ? 'is-invalid' : ''" z>
            
                        <div class="invalid-feedback" v-if="facebookHasError">
                            {{ errors.facebook[0]}}
                        </div>    
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label id="contact_number">Phone Number</label>
                        <input value="" type="text" v-mask="masks.phone" placeholder="09XXXXXXXXX" v-model="fields.contact_number" id="contact_number" class="form-control" v-bind:class="phoneHasError ? 'is-invalid' : ''" z>
                    
                        <div class="invalid-feedback" v-if="phoneHasError">
                            {{ errors.contact_number[0]}}
                        </div>   
                    </div> 
                </div>
                <div class="row px-3">
            
                    <div class="form-group col-md-3 px-3">
                        <label for="birthday">Birthday</label>
                        <date-picker id="birthday" :default_value="fields.birthday" v-model="fields.birthday" v-bind:class="birthdayHasError ? 'is-invalid' : ''" v-bind:has-error="birthdayHasError ? 'is-invalid' : ''"  @datePicked="getDate($event, 'birthday')" ></date-picker>
                        <div class="invalid-feedback" v-if="birthdayHasError">
                            {{ errors.birthday[0]}}
                        </div>  
                    </div>
            
                    <div class="form-group col-md-4 px-3">
                        <label for="birthplace">Birthplace</label>
                        <input value="" id="birthplace" type="text" class="form-control" v-model="fields.birthplace" v-bind:class="birthplaceHasError ? 'is-invalid' : ''" z autocomplete="birthplace">
                        <div class="invalid-feedback" v-if="birthplaceHasError">
                            {{ errors.birthplace[0]}}
                        </div>  
                    </div>
            
            
                    <div class="form-group col-md-5 px-3">
                        <label for="education">Educational Attainment</label>
                        <div class="select" v-bind:class="educationHasError ? 'is-invalid' : ''">
                            <select v-model="fields.education" id="education" class="form-control" v-bind:class="educationHasError ? 'is-invalid' : ''">
                                <option value="">CHOOSE AN OPTION</option>
                                <option value="COLLEGE">COLLEGE</option>
                                <option value="VOCATIONAL">VOCATIONAL</option>
                                <option value="HIGH SCHOOL">HIGH SCHOOL</option>
                                <option value="ELEMENTARY">ELEMENTARY</option>
                            </select>
                        </div>
                        <div class="invalid-feedback" v-if="educationHasError">
                            {{ errors.education[0]}}
                        </div>  
                    </div>
                </div>
                <h3 class="px-3 my-4 title text-2xl">Address Information</h3>
                <div class="row px-3">
                    <div class="form-group px-3 col-md-9">
                        <label for="street_address">Street Address</label>
                        <input value="" type="text" v-model="fields.street_address" id="street_address" class="form-control" v-bind:class="streetHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="streetHasError">
                            {{ errors.street_address[0]}}
                        </div>          
                    </div>
                    <div class="form-group col-md-3 px-2">
                        <label for="barangay_address">Barangay</label>
                        <input value="" type="text" v-model="fields.barangay_address" id="barangay_address" class="form-control" v-bind:class="barangayHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="barangayHasError">
                            {{ errors.barangay_address[0]}}
                        </div>      
                    </div>
                </div>
                <div class="row px-3">
                    <div class="form-group col-md-4">
                        <label for="city_address">City</label>
                        <input value="" type="text" v-model="fields.city_address" id="city_address" class="form-control" v-bind:class="cityHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="cityHasError">
                            {{ errors.city_address[0]}}
                        </div>
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="province_address">Province</label>
                        <input value="" type="text" v-model="fields.province_address" id="province_address" class="form-control" v-bind:class="provinceNameHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="provinceNameHasError">
                            {{ errors.province_address[0]}}
                        </div>
                    </div>
            
            
                    <div class="form-group col-md-4 px-2">
                        <label for="zipcode">Postal/Zip code</label>
                        <input value="" type="text" v-mask="masks.zipcode" v-model="fields.zipcode" id="zipcode" class="form-control" v-bind:class="zipCodeHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="zipCodeHasError">
                            {{ errors.zipcode[0]}}
                        </div>
                    </div>
                </div>
                <h3 class="px-3 my-3 title text-2xl">Family Information</h3>	
                <div class="row px-3"> 
                    <div class="form-group col-md-12">
                        <label for="business_address">Full Business Address</label>
                        <input value="" type="text" v-model="fields.business_address" id="business_address" class="form-control" v-bind:class="businessAddressHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="businessAddressHasError">
                            {{ errors.business_address[0]}}
                        </div>
                    </div>
                </div>
            
                <div class="row px-3">
                    <div class="form-group px-3 col-md-3">
                        <label for="number_of_dependents">No. of dependents</label>
                        <input value="" type="text" v-mask="masks.dependents" v-model="fields.number_of_dependents" id="number_of_dependents" class="form-control" v-bind:class="dependentsHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="dependentsHasError">
                            {{ errors.number_of_dependents[0]}}
                        </div>        
                        
                    </div>
                    <div class="form-group px-3 col-md-3">
                        <label for="household_size">Household Size</label>
                        <input type="text" v-mask="masks.household_size" v-model="fields.household_size" id="household_size" class="form-control" v-bind:class="householdHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="householdHasError">
                            {{ errors.household_size[0]}}
                        </div>
                    </div>
                    <div class="form-group px-3 col-md-3">
                        <label for="years_of_stay_on_house">Years of Residency</label>
                        <input type="text" v-mask="masks.years_of_stay_on_house" v-model="fields.years_of_stay_on_house" id="years_of_stay_on_house" class="form-control" v-bind:class="yearsOfStayHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="yearsOfStayHasError">
                            {{ errors.years_of_stay_on_house[0]}}
                        </div>
                    </div>
            
                    <div class="form-group px-3 col-md-3 col-md-offset-2">
                        <label for="house_type">House Type</label>
                        <select v-model="fields.house_type" id="house_type" class="form-control" v-bind:class="houseTypeHasError ? 'is-invalid' : ''">
                            <option value="">CHOOSE AN OPTION</option>
                            <option value="OWNED">OWNED</option>
                            <option value="RENTED">RENTED</option>
                        </select>
                        <div class="invalid-feedback" v-if="houseTypeHasError">
                            {{ errors.house_type[0]}}
                        </div>
                    </div>
            
                </div>
            
            
                <div class="row px-3">
                    <div class="form-group col-md-6">
                        <label for="spouse_name">Spouse Name</label>
                        <input value="" type="text" v-model="fields.spouse_name" class="form-control" id="spouse_name" v-bind:class="spouseNameHasError ? 'is-invalid' : ''" z>
                        <div class="invalid-feedback" v-if="spouseNameHasError">
                            {{ errors.spouse_name[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="spouse_contact_number">Spouse Contact Number</label>
                        <input value="" type="text" v-mask="masks.phone" v-model="fields.spouse_contact_number" class="form-control" id="spouse_contact_number" v-bind:class="spouseContactHasError ? 'is-invalid' : ''" placeholder="09XXXXXXXXX" z>
                        <div class="invalid-feedback" v-if="spouseContactHasError">
                            {{ errors.spouse_contact_number[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4 col-md-offset-8">
                        <label for="spouse_birthday">Spouse Birthday</label>
                    
                        <date-picker  :default_value="fields.spouse_birthday" v-bind:class="houseTypeHasError ? 'is-invalid' : ''" v-bind:has-error="birthdayHasError ? 'is-invalid' : ''"  v-model="fields.spouse_birthday" name="spouse_birthday" id="spouse_birthday"  @datePicked="getDate($event, 'spouse_birthday')"></date-picker>
                        <div class="invalid-feedback" v-if="spouseBirthdayHasError">
                            {{ errors.spouse_birthday[0]}}
                        </div>
                    </div>
                </div>
            
                <h3 class="title text-2xl px-3 my-4">Statutories</h3>
            
                <div class="row px-3">
                    <div class="form-group col-md-4">
                        <label>TIN</label>
                        <input value="" type="text" v-mask="masks.tin" v-model="fields.tin" id="tin" v-bind:class="tinHasError ? 'is-invalid' : ''"  class="form-control">
                        <div class="invalid-feedback" v-if="tinHasError">
                            {{ errors.tin[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>SSS</label>
                        <input value="" type="text" v-mask="masks.sss" v-model="fields.sss" id="sss" v-bind:class="sssHasError ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="sssHasError">
                            {{ errors.sss[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>UMID</label>
                        <input value="" type="text" v-mask="masks.umid" v-model="fields.umid" id="umid" v-bind:class="umidHasError ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="umidHasError">
                            {{ errors.umid[0]}}
                        </div>
                    </div>
                    <div class="form-group col-md-6 col-md-offset-6">
                        <label>Mothers Maiden Name</label>
                        <input value="" type="text" v-model="fields.mother_maiden_name" v-bind:class="motherMaidenNameHasError ? 'is-invalid' : ''" id="mother_maiden_name" class="form-control" z>
                        <div class="invalid-feedback" v-if="motherMaidenNameHasError">
                            {{ errors.mother_maiden_name[0]}}
                        </div>
                    </div>
                </div>
            
                <h3 class="px-3 my-4 title text-2xl">Employment Income</h3>
            
                <div class="row px-3">
                    <h3 class="col-md-6 px-3 my-2 title text-2xl">Personal</h3>
                    <h3 class="col-md-6 my-2 title text-2xl">Spouse</h3>
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
                            <div >
                                <select class="form-control" v-model="fields.service_type" id="service_type">
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
                            <label for="service_type_gross_income">Monthly Income</label>
                            <input type="number" step ="0.01" id="service_type_gross_income" v-model="fields.service_type_monthly_gross_income" class="form-control">
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
                            <label for="spouse_service_type_gross_income">Monthly Income</label> 
                            <input type="number" step ="0.01" id="spouse_service_type_gross_income" v-model="fields.spouse_service_type_monthly_gross_income" value="" class="form-control">
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
            
                <div class="row px-3 my-3">
                    <div class="form-group col-md-4 col-md-offset-8">
                        <label class="">Total household income</label>
                        <input type="text" class="form-control" v-bind:class="totalHouseholdIncomeHasError ? 'is-invalid' : ''"  readonly="true" :value="displayed_total_household_income">
                        <div class="invalid-feedback" v-if="totalHouseholdIncomeHasError">
                            {{ errors.total_household_income[0]}}
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
                <img :src="fields.profile_picture_path_preview" class="img-thumbnail" v-bind:class="profilePhotoHasError ? 'is-invalid' : ''"  alt="Cinque Terre" style="height:100% !importante" > 
                <div class="file-input text-center">
                    <span class="position-relative btn btn-rose btn-round btn-file">
                        <span class="fileinput-new">Image</span>
                        <input value="" type="file" class="attachment" name="profile_picture_path" @change="onFileSelected($event,'profile_picture_path')">
                    </span>
                </div>
                <div class="invalid-feedback" v-if="profilePhotoHasError">
                    {{ errors.profile_picture_path[0]}}
                </div>  
            </div>
            <div  class="file-input-signature d-block text-center position-relative">
                <img :src="fields.signature_path_preview" class="img-thumbnail" v-bind:class="profilePhotoHasError ? 'is-invalid' : ''" alt="Cinque Terre" > 
                <div class="file-input text-center">
                    <span class="position-relative btn btn-rose btn-round btn-file">
                    <span class="fileinput-new">Signature</span>
                    <input value="signature_path" type="file" class="attachment" name="signature_path" @change="onFileSelected($event,'signature_path')">
                    </span>
                </div>
                <div class="invalid-feedback" v-if="signaturePhotoHasError">
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
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        SelectComponentV2,
        Loading 
    },
    props: ["client"],
    data(){
        return {
            masks: {
                tin:'###-###-###-###',
                sss:'##-#######-#',
                umid:'####-#######-#',
                phone: '09##-###-####',
                zipcode: '####',
                dependents: '##',
                household_size:'##',
                years_of_stay_on_house:'###'
            },
            isLoading:false,
            fields: {
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
                'spouse_birthday':null,
                'tin':"",
                'sss':"",
                'umid':"",
                'mother_maiden_name':"",
                'photo_changed' :false,
                'signature_changed' :false,


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

                'notes':"",

                'profile_picture_path_preview':null,
                'signature_path_preview': null,


                'profile_picture_path': null,
                'signature_path': null,
            },
            errors: {

            },
            
        }
    },
    computed:{
        clientInfo(){
            return JSON.parse(this.client)
        },
        total_household_income(){
            var total = 
            parseFloat(this.fields.service_type_monthly_gross_income === NaN || this.fields.service_type_monthly_gross_income === null  ? 0 : this.fields.service_type_monthly_gross_income) +
            parseFloat(this.fields.employed_monthly_gross_income === NaN || this.fields.employed_monthly_gross_income === null ? 0 : this.fields.employed_monthly_gross_income) +
            parseFloat(this.fields.spouse_service_type_monthly_gross_income === NaN || this.fields.spouse_service_type_monthly_gross_income === null ? 0 : this.fields.spouse_service_type_monthly_gross_income) +
            parseFloat(this.fields.spouse_employed_monthly_gross_income === NaN || this.fields.spouse_employed_monthly_gross_income === null ? 0 : this.fields.spouse_employed_monthly_gross_income) +
            parseFloat(this.fields.remittance_amount === NaN || this.fields.remittance_amount ===null ? 0 : this.fields.remittance_amount) +
            parseFloat(this.fields.pension_amount === NaN || this.fields.pension_amount === null ? 0 : this.fields.pension_amount)
            
            if(isNaN(total)){
                return 'Use positive integers for amounts only';
            }
            return (total)
        },
        displayed_total_household_income(){
            return '₱ '+this.number_format(this.total_household_income)
        },
        hasErrors(){
            return Object.keys(this.errors).length > 0;
        },
        officeHasError(){
            return this.errors.hasOwnProperty('office_id')
        },
        signaturePhotoHasError(){
            return this.errors.hasOwnProperty('signature_path')
        },
        profilePhotoHasError(){
            return this.errors.hasOwnProperty('profile_picture_path')
        },
        firstNameHasError(){
            return this.errors.hasOwnProperty('firstname')
        },
        middleNameHasError(){
            return this.errors.hasOwnProperty('middlename')
        },
        lastNameHasError(){
            return this.errors.hasOwnProperty('lastname')
        },
        suffixNameHasError(){
            return this.errors.hasOwnProperty('suffixName')
        },
        nickNameHasError(){
            return this.errors.hasOwnProperty('nickname')
        },
        genderHasError(){
            return this.errors.hasOwnProperty('gender')
        },
        civilStatusHasError(){
            return this.errors.hasOwnProperty('civil_status')
        },
        facebookHasError(){
            return this.errors.hasOwnProperty('facebook')
        },
        phoneHasError(){
            return this.errors.hasOwnProperty('contact_number')
        },
        birthdayHasError(){
            return this.errors.hasOwnProperty('birthday')

        },
        birthplaceHasError(){
            return this.errors.hasOwnProperty('birthplace')
        },
        educationHasError(){
            return this.errors.hasOwnProperty('education')
        },
        streetHasError(){
            return this.errors.hasOwnProperty('street_address')
        },
        barangayHasError(){
            return this.errors.hasOwnProperty('barangay_address')
        },
        cityHasError(){
            return this.errors.hasOwnProperty('city_address')
        },
        provinceNameHasError(){
            return this.errors.hasOwnProperty('province_address')
        },
        zipCodeHasError(){
            return this.errors.hasOwnProperty('zipcode')
        },
        businessAddressHasError(){
            return this.errors.hasOwnProperty('business_address')
        },
        dependentsHasError(){
            return this.errors.hasOwnProperty('number_of_dependents')
        },
        householdHasError(){
            return this.errors.hasOwnProperty('household_size')
        },
        yearsOfStayHasError(){
            return this.errors.hasOwnProperty('years_of_stay_on_house')
        },
        houseTypeHasError(){
            return this.errors.hasOwnProperty('house_type')
        },
        spouseNameHasError(){
            return this.errors.hasOwnProperty('spouse_name')
        },
        spouseContactHasError(){
            return this.errors.hasOwnProperty('spouse_contact_number')
        },
        spouseBirthdayHasError(){
            return this.errors.hasOwnProperty('spouse_birthday')
        },
        tinHasError(){
            return this.errors.hasOwnProperty('tin')
        },
        sssHasError(){
            return this.errors.hasOwnProperty('sss')
        },
        umidHasError(){
            return this.errors.hasOwnProperty('umid')
        },
        motherMaidenNameHasError(){
            return this.errors.hasOwnProperty('mother_maiden_name')
        },
        totalHouseholdIncomeHasError(){
            return this.errors.hasOwnProperty('total_household_income')
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
    watch : {
        'fields.is_self_employed' : function (newVal,oldVal){
           if(!newVal){               
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
    },
    created(){
        this.populateData();
    },
    methods: {

        number_format(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },
        populateData(){
            var vm = this
            $.each(vm.clientInfo,function(k,v){
                if(k =="household_income"){
                    $.each(v,function(hK,hV){
                        var key = hK.replace("household_income","")
                        vm.fields[key] = hV
                    })
                }else{
                    if(k=="profile_picture_path"){
                        vm.fields.profile_picture_path_preview = location.origin+ '/'+ v
                    }
                    if(k=="signature_path"){
                        vm.fields.signature_path_preview = location.origin+ '/'+ v
                    }else{
                        vm.fields[k] = v
                    }

                }

            })
            
        },
        upload(){
            let form = new FormData();
            // $.each(this.fields,function(k,v){
                
            // });
            
            // form.append('image', this.fields.profile_picture_path,this.fields.profile_picture_path);
            // console.log(form.get);
            // axios.post('/upload', form)
            //     .then(res=>{
            //         console.log(res);   
            //     })
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
                  this.fields.photo_changed = true
                  this.fields.profile_picture_path_preview = URL.createObjectURL(file)
              }
              if(id == "signature_path"){
                  this.fields.signature_changed = true
                  this.fields.signature_path_preview = URL.createObjectURL(file)
              }
              
              this.fields[id] = file;

        },
        toClient(){
            return "/client/"+this.clientInfo.client_id
        },
        submit(){
            this.errors = {}
            var vm = this
            vm.fields['total_household_income'] = vm.total_household_income
            let formData = new FormData();
            
            $.each(vm.fields, function(k,v){    
                if(k!="profile_picture_path" && k!="signature_path") {
                    formData.append(k,v)
                }

                if(k == "profile_picture_path"){
                    if(vm.fields.photo_changed){
                        formData.append(k,v)
                    }else{
                    
                    }
                }
                if(k=="signature_path"){
                    if(vm.fields.signature_changed){
                        formData.append(k,v)
                    }
                }

            });
           
            this.isLoading = true
            axios.post('/edit/client', formData)
                .then(res=>{
                    this.isLoading = false
                    setTimeout(()=>{
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK',
                        allowEnterKey: true // default value
                    })
                    .then(res=>{
                        location.href = this.toClient()
                    })
                    },1)
 

                })
                .catch(error=>{
                    this.isLoading = false
                    this.errors = error.response.data.errors || {}
                    if(this.duplicateClient){
                        Swal.fire({
                            icon: 'error',
                            title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">OOPPPSSSSS!</span>',
                            text: this.errors.client.msg,
                            footer: '<a href="#">Client ['+this.errors.client.client_id+'] already existing at: ' +this.errors.client.exists_at +'</a>'
                            
                            
                        })
                    }
                })
        },
    },

}
</script>