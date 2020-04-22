using System;
using System.Collections.Generic;
using ClientBL.DOM;
using System.Threading.Tasks;
using ClientBL.Exceptions;
using Newtonsoft.Json;

namespace ClientBL
{
    public class ListManager
    {

        private Configurations conf;

        public ListManager()
        {
            conf = Configurations.Instance;
        }


        public List LoadList(string name = ""){

            if( name == ""){
                throw new InvalidParameterException("Name must be set in LoadList");
            }

            var http = HttpFactory.GetHttpClient();

            var resp = http.GetAsync(conf.BaseURL + "lists/"+ name + "?format=json").Result;


            if (resp.StatusCode == System.Net.HttpStatusCode.OK)
            {

                var jsonResp = resp.Content.ReadAsStringAsync().Result;

                List l = new List();

                l.Name = name;

                l.Values = JsonConvert.DeserializeObject<List<String>>(jsonResp);

                return l;

            }else{
                throw new HttpRequestFailedException("Return code:" + resp.StatusCode);
            }


            //return null;
        }

    }
}
