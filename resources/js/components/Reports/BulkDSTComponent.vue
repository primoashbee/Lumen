<template>
  <div>
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h3 class="h3">Client Report</h3>
            <div class="row">
              <div class="col-lg-6">
                <label for="" style="color: white" class="lead mr-2"
                  >Branch:</label
                >
                <v2-select
                  @officeSelected="assignOffice"
                  style="width: 100%"
                ></v2-select>
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
                    >Disbursement Date</label
                >
                <input
                    type="date"
                    class="form-control"
                    v-model="request.disbursement_date"
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
                  <td><p class="title">Type</p></td>
                  <td><p class="title">Principal</p></td>
                  <td><p class="title">Interest</p></td>
                  <td><p class="title">Fees</p></td>
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
                    <p class="title">{{ item.client_fullname }}</p>
                  </td>
                  <td>
                    <p class="title">{{ item.type }}</p>
                  </td>
                  <td>
                    <p class="title">{{ moneyFormat(item.principal) }}</p>
                  </td>
                  <td>
                    <p class="title">{{ moneyFormat(item.interest) }}</p>
                  </td>
                  <td>
                    <p class="title">{{ moneyFormat(item.fees) }}</p>
                  </td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><p class="title"># of Clients</p></td>
                  <td>
                    <p class="title">{{ list.summary.total }}</p>
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
        per_page: 25,
        disbursement_date: null,
        page: 1,
      },
      url: "/wApi/reports/dst",
      exportable: false,
    };
  },
  mounted() {
    this.request.is_summarized = false;
  },
  methods: {
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
          if (this.list.summary.total > 0) {
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