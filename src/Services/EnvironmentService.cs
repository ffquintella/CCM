using System.Collections.Generic;
using System.Net;
using Domain;
using RestSharp;
using Services.Helpers;

namespace Services
{
    public class EnvironmentService: BaseService
    {
        public List<Environment> GetAll()
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Environments");

            var response = client.Get<List<Environment>>(request);

            if (response.StatusCode == HttpStatusCode.OK)
            {
                return response.Data;
            }
            else
            {
                return null;
            }

        }    
    }
}