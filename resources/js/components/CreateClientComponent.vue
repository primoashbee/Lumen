<template>
    <div class="row">
        <div class="col-9">
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
                    </form>
                </div>
            </div>
        </div>

    </div>
</template>