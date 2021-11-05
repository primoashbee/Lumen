<template>
  <div>
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h3 class="h3">Loan In Arrears Report - Principal</h3>
            <div class="row">
              <div class="col-lg-6">
                <label for="" style="color: white" class="lead mr-2"
                  >Branch:</label
                >
                <v2-select
                  @officeSelected="assignOffice"
                  style="width: 100%"
                ></v2-select>
                <div class="w-100">
                  <label for="" style="color:white" class="lead">Products</label>
                  <products  :list="'loan'" status="1" multi_values="true" @productSelected="productSelected" ></products>
              </div>
              </div>
              <div class="col-lg-6 text-right">
                <button class="btn btn-primary" @click="download" v-if="exportable">
                Export Report
                </button>
              </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-4">
                <label for="" style="color: white" class="lead mr-2"
                    >Date</label
                >
                <input
                    type="date"
                    class="form-control"
                    v-model="request.date"
                />
                </div>
            </div>
            <button class="btn btn-primary mt-4" @click="search">Search</button>
            
          </div>
          <div class="card-body">
            <table class="table table-striped">
              <thead>
                <tr>
                  <td><p class="title">Level</p></td>
                  <td><p class="title">Client ID</p></td>
                  <td><p class="title">Name</p></td>
                  <td><p class="title">Product</p></td>
                  <td><p class="title">PAR Amount</p></td>
                </tr>
              </thead>
              <tbody v-if="list.hasOwnProperty('data')">
                <tr v-for="(item, key) in list.data.data" :key="key">
                  <td>
                    <p class="title">{{ item.level }}</p>
                  </td>
                  <td>
                    <p class="title">{{ item.client_id }}</p>
                  </td>
                  <td>
                    <p class="title">{{ item.code }}</p>
                  </td>
                  <td>
                    <p class="title">{{ item.fullname }}</p>
                  </td>
                  <td>
                    <p class="title">{{ moneyFormat(item.par_amount) }}</p>
                  </td>
                </tr>
                <tr>
                  <td><p class="title">Total Amount</p></td>
                  <td><p class="title">{{moneyFormat(list.summary.total_amount)}}</p></td>
                  <td></td>
                  <td><p class="title"># of Clients</p></td>
                  <td>
                    <p class="title">{{ list.data.total }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
            <paginator
              :dataset="list.data"
              @pageSelected="pageSelected"
              v-if="list.hasOwnProperty('data')"
            ></paginator>
          </div>
        </div>
      </div>
    </div>
    <loading :is-full-page="true" :active.sync="isLoading"></loading>
  </div>
</template>
<script>
import _ from "lodash";
import Paginator from "./../PaginatorComponent";
import Loading from "vue-loading-overlay";
export default {
  props: ["report_class"],
  components: {
    Loading,
  },
  data() {
    return {
      isLoading: false,
      exportable: false,
      list: [],
      request: {
        is_summarized: false,
        office_id: null,
        products:null,
        per_page: 25,
        date: null,
        page: 1,
      },
      url: "/wApi/reports/loan_in_arrears_principal",
      exportable: false,
    };
  },
  mounted() {
    this.request.is_summarized = false;
  },
  methods: {
    productSelected(value){
        this.request.products = _.map(value,'id');
        // this.request.products = _.map(_.pick(['type','id'], value));
    },
    moneyFormat(value) {
      return moneyFormat(value);
    },
    pageSelected(page) {
      this.request.page = page;
      this.search();
    },
    statusSelected(value, field = null) {
      this.request[field] = value;
    },
    assignOffice(value) {
      this.request.office_id = value["id"];
    },
    search() {
      this.exportable = false;
      axios
        .post(this.url + "?page=" + this.request.page, this.request)
        .then((res) => {
          this.list = res.data;
          if (this.list.data.total > 0) {
            this.exportable = true;
          }
        });
    },
    download() {
      var data = Object.assign({}, this.request);
      data["export"] = true;
      this.isLoading = true;
      axios
        .post(this.url + "?page=" + this.request.page, data, {
          headers: {
            "Content-Disposition": "attachment; filename=template.xlsx",
            "Content-Type": "application/json",
          },
          responseType: "arraybuffer",
        })
        .then((res) => {
          this.isLoading = false;
          const url = window.URL.createObjectURL(
            new Blob([res.data], { type: "application/vnd.ms-excel" })
          );
          const link = document.createElement("a");
          link.href = url;
          link.setAttribute("download", res.headers.filename);
          document.body.appendChild(link);
          link.click();
          this.isLoading = false;
        })
        .catch((err) => {
          this.isLoading = false;
        });
    },
  },
};
</script>