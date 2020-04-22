using System;
using System.Collections.Generic;
using ClientBL.DOM;
using System.Threading.Tasks;
using ClientBL.Exceptions;
using Newtonsoft.Json;
using ClientBL.Tools;
using System.Text;
using System.Net.Http;

namespace ClientBL
{
    public class AppManager
    {

        private Configurations conf;

        public AppManager()
        {
            conf = Configurations.Instance;
        }


        public List<App> Apps { get; set; } = new List<App>();

        public List<App> LoadApps()
        {
            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "apps?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                List<String> appsS = JsonConvert.DeserializeObject<List<String>>(jsonResp);

                List<App> apps = new List<App>();

                foreach(var a in appsS){
                    var ap = new App();
                    ap.Name = a;
                    ap.Environments = new List<string>();
                    apps.Add(ap);
                }

                Apps = apps;

                return apps;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }


        }

        public App LoadApp(string name = ""){

            if( name == ""){
                throw new InvalidParameterException("Name must be set in LoadServer");
            }

            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "apps/"+ name + "?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                App a = JsonConvert.DeserializeObject<App>(jsonResp);

                return a;

            }else{
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }


            //return null;
        }

        public OperationResult Save(App app, bool update = false)
        {

            if (app == null) return OperationResult.InvalidParameters;

            var serializerSettings = new JsonSerializerSettings();

            serializerSettings.ContractResolver = new LowercaseContractResolver();

            var jsonRep = JsonConvert.SerializeObject(app, serializerSettings);

            var http = HttpFactory.GetHttpClient();

            var content = new StringContent(jsonRep, Encoding.UTF8, "application/json");

            HttpResponseMessage resp;

            if (update)
            {
                resp = http.Post(conf.BaseURL + "apps/" + app.Name, content);
                //return OperationResult.NotImplemented;
            }
            else
            {
                resp = http.Put(conf.BaseURL + "apps/" + app.Name, content);
            }

            if (resp.StatusCode == System.Net.HttpStatusCode.Created || resp.StatusCode == System.Net.HttpStatusCode.OK)
            {
                return OperationResult.OK;
            }
            else
            {
                if (resp.StatusCode == System.Net.HttpStatusCode.InternalServerError) return OperationResult.ServerError;
            }

            return OperationResult.UnidentifiedError;
        }

        public OperationResult Delete(App app)
        {

            if (app == null) return OperationResult.InvalidParameters;

            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.Delete(conf.BaseURL + "apps/" + app.Name);


            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {
                return OperationResult.OK;
            }
            else
            {
                if (resp.StatusCode == System.Net.HttpStatusCode.InternalServerError) return OperationResult.ServerError;
            }

            return OperationResult.UnidentifiedError;
        }

    }
}
