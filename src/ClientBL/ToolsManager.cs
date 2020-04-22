using System;
using System.Collections.Generic;
using ClientBL.DOM;
using System.Net.Http;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;
using ClientBL.Exceptions;
namespace ClientBL
{
    public class ToolsManager
    {

        private Configurations conf;

        public ToolsManager()
        {
            conf = Configurations.Instance;
        }



        public List<String> CleanUpOldRelations()
        {
            var http = HttpFactory.GetHttpClient();

            HttpResponseMessage resp;

            resp = http.GetAsync(conf.BaseURL + "tools/cleanUpOldRelations?format=json").Result;


            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {
                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                List<String> results = JsonConvert.DeserializeObject<List<String>>(jsonResp);

                return results;
            }

            return new List<string>();

        }

    }
}
