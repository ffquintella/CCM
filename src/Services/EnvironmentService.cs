using System.Collections.Generic;
using System.Net;
using System.Threading.Tasks;
using Domain;
using Domain.Protocol;
using Microsoft.Extensions.Hosting;
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
            
        public Task<ObjectOperationResponse> SaveAsync(List<Environment> envs)
        {
            return null;
        }
    }

 }