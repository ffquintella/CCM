using System;
using System.Collections.Generic;
using ClientBL.DOM;
using System.Threading.Tasks;
using ClientBL.Exceptions;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System.Linq;
using ClientBL.Tools;
using System.Text;
using System.Net.Http;
using System.Net;

namespace ClientBL
{
    public class ConfigurationManager
    {

        private Configurations conf;

        public ConfigurationManager()
        {
            conf = ClientBL.Configurations.Instance;
        }


        public List<Configuration> Configurations { get; set;  } = new List<Configuration>();


        public List<Configuration> LoadConfigurations(string appName)
        {
            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;
            if(appName != null)
                resp = http.GetAsync(conf.BaseURL + "configurations?format=json&app=" + WebUtility.UrlEncode(appName)).Result;
            else 
                resp = http.GetAsync(conf.BaseURL + "configurations?format=json").Result;
            
            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                if (jsonResp == "\"Success\"") return new List<Configuration>();

                List<String> configsS = JsonConvert.DeserializeObject<List<String>>(jsonResp);

                List<Configuration> configs = new List<Configuration>();

                foreach (var c in configsS)
                {
                    var config = new Configuration();
                    config.Name = c;
                    config.Values = new Dictionary<string, string>();

                    configs.Add(config);
                }

                Configurations = configs;

                return configs;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }
        }

        public List<Configuration> LoadConfigurations() {
            return this.LoadConfigurations("");
        }


        public Configuration LoadConfiguration(string name){
            if (name == "")
            {
                throw new InvalidParameterException("Name must be set in LoadCredential");
            }

            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "configurations/" + name + "?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                Configuration c = JsonConvert.DeserializeObject<Configuration>(jsonResp);

                return c;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }
        }

        public OperationResult Save(Configuration config, bool update = false)
        {
            if (config == null) return OperationResult.InvalidParameters;

            var serializerSettings = new JsonSerializerSettings();

            serializerSettings.ContractResolver = new LowercaseContractResolver();

            var jsonRep = JsonConvert.SerializeObject(config, serializerSettings);

            var jsonRep2 = JsonConvert.SerializeObject(config);

            var job = JObject.Parse(jsonRep);
            var job2 = JObject.Parse(jsonRep2);

            if(update)
                job.Remove("type");
            
            job["values"] = job2["Values"];


            var http = HttpFactory.GetHttpClient();

            var content = new StringContent(job.ToString(Formatting.None), Encoding.UTF8, "application/json");

            HttpResponseMessage resp;

            if (update)
            {
                resp = http.Post(conf.BaseURL + "configurations/" + config.Name, content);
                //return OperationResult.NotImplemented;
            }
            else
            {
                resp = http.Put(conf.BaseURL + "configurations/" + config.Name, content);
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
          

        public OperationResult Delete(Configuration config)
        {

            if (config == null) return OperationResult.InvalidParameters;

            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.Delete(conf.BaseURL + "configurations/" + config.Name);


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
