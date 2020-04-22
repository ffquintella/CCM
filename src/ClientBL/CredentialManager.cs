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
    public class CredentialManager
    {

        private Configurations conf;

        public CredentialManager()
        {
            conf = Configurations.Instance;
        }


        public List<Credential> Credentials { get; set;  } = new List<Credential>();


        public List<Credential> LoadCredentials(string appName)
        {
            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;
            if(appName != null)
                resp = http.GetAsync(conf.BaseURL + "credentials?format=json&app=" + WebUtility.UrlEncode(appName)).Result;
            else 
                resp = http.GetAsync(conf.BaseURL + "credentials?format=json").Result;
            
            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                if (jsonResp == "\"Success\"") return new List<Credential>();

                List<String> credsS = JsonConvert.DeserializeObject<List<String>>(jsonResp);

                List<Credential> creds = new List<Credential>();

                foreach (var c in credsS)
                {
                    var cred = new Credential();
                    cred.Name = c;
                    cred.Values = new Dictionary<string, string>();

                    creds.Add(cred);
                }

                Credentials = creds;

                return creds;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }
        }

        public List<Credential> LoadCredentials() {
            return this.LoadCredentials("");
        }


        public Credential LoadCredential(string name){
            if (name == "")
            {
                throw new InvalidParameterException("Name must be set in LoadCredential");
            }

            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "credentials/" + name + "?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                Credential c = JsonConvert.DeserializeObject<Credential>(jsonResp);

                return c;

            }
            else
            {
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }
        }

        public OperationResult Save(Credential cred, bool update = false)
        {
            if (cred == null) return OperationResult.InvalidParameters;

            var serializerSettings = new JsonSerializerSettings();

            serializerSettings.ContractResolver = new LowercaseContractResolver();

            var jsonRep = JsonConvert.SerializeObject(cred, serializerSettings);

            var jsonRep2 = JsonConvert.SerializeObject(cred);

            var job = JObject.Parse(jsonRep);
            var job2 = JObject.Parse(jsonRep2);

            bool vault = false;

            if (job["type"].ToString() == "vault"){
                vault = true;
            }


            if(update)
                job.Remove("type");

            if(vault){
                job.Remove("values");
                //job.Remove("VaultIds");
                job["vaultIds"] = job2["VaultIds"];
            }else{
                job.Remove("vaultids");
                job["values"] = job2["Values"]; 
            }




            /*

            var jo2 = job["values"];

            foreach(var jo3 in ((JObject)jo2).Properties().ToList() ){
                var val = (JProperty)jo3;
                var newName = val.Name[0].ToString().ToUpper() + val.Name.Substring(1); 
                jo3.Replace(new JProperty(newName, val.Value));

            }*/

            var http = HttpFactory.GetHttpClient();

            var content = new StringContent(job.ToString(Formatting.None), Encoding.UTF8, "application/json");

            HttpResponseMessage resp;

            if (update)
            {
                resp = http.Post(conf.BaseURL + "credentials/" + cred.Name, content);
                //return OperationResult.NotImplemented;
            }
            else
            {
                resp = http.Put(conf.BaseURL + "credentials/" + cred.Name, content);
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
          

        public OperationResult Delete(Credential cred)
        {

            if (cred == null) return OperationResult.InvalidParameters;

            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.Delete(conf.BaseURL + "credentials/" + cred.Name);


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
