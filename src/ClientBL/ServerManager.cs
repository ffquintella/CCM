using System;
using System.Collections.Generic;
using ClientBL.DOM;
using ClientBL.Tools;
using System.Threading.Tasks;
using ClientBL.Exceptions;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using System.Net.Http;
using System.Text;

namespace ClientBL
{
    public class ServerManager
    {

        private Configurations conf;

        public ServerManager()
        {
            conf = Configurations.Instance;
        }

        public List<Server> Servers { get; set; } = new List<Server>();

        public  List<Server> LoadServers(){
            var http = HttpFactory.GetHttpClient();



            var resp =  http.GetAsync(conf.BaseURL + "servers?format=json").Result;

            if(resp.StatusCode == System.Net.HttpStatusCode.OK){

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                List<Server> servers = JsonConvert.DeserializeObject<List<Server>>(jsonResp);

                Servers = servers;

                return Servers;

            }else{
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }


        }

        public Server LoadServer(string name = ""){

            if( name == ""){
                throw new InvalidParameterException("Name must be set in LoadServer");
            }

            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "servers/"+ name + "?format=json").Result;

            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                var job = JObject.Parse(jsonResp);
                var addDict = false;
                if(job["assignments"].Type == JTokenType.Array){
                    job.Remove("assignments");
                    addDict = true;
                }

                Server s = job.ToObject<Server>();

                if(addDict) s.Assignments = new Dictionary<string, List<string>>();

                //Server s = JsonConvert.DeserializeObject<Server>(jsonResp);

                return s;

            }else{
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }


        }

        public OperationResult Save(Server server, bool update = false){

            if (server == null) return OperationResult.InvalidParameters;


            var serializerSettings = new JsonSerializerSettings();

            serializerSettings.ContractResolver = new LowercaseContractResolver();


            var jsonRep = JsonConvert.SerializeObject(server, serializerSettings);

            var http = HttpFactory.GetHttpClient();

            var content = new StringContent(jsonRep, Encoding.UTF8, "application/json");

            HttpResponseMessage resp;

            if(update){
                resp = http.Post(conf.BaseURL + "servers/" + server.Name, content);
                //return OperationResult.NotImplemented;
            }else{
                resp = http.Put(conf.BaseURL + "servers/" + server.Name, content);
            }

            if(resp.StatusCode == System.Net.HttpStatusCode.Created || resp.StatusCode == System.Net.HttpStatusCode.OK){
                return OperationResult.OK;
            }else{
                if (resp.StatusCode == System.Net.HttpStatusCode.InternalServerError) return OperationResult.ServerError;
            }

            return OperationResult.UnidentifiedError;
        }

        public OperationResult Delete (Server server){

            if (server == null) return OperationResult.InvalidParameters;

            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.Delete(conf.BaseURL + "servers/" + server.Name);

 
            if (resp.StatusCode == System.Net.HttpStatusCode.OK )
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
