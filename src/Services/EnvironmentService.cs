using System;
using System.Collections.Generic;
using System.Net;
using System.Threading.Tasks;
using Domain.Protocol;
using Microsoft.Extensions.Hosting;
using Newtonsoft.Json;
using RestSharp;
using Services.Helpers;
using Environment = Domain.Environment;

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
            
        public async Task<Tuple<ObjectOperationResponse, List<Environment>>> SaveAsync(List<Environment> envs)
        {
            var client = RestClientHelper.GetAuthenticatedClient();
            var request = new RestRequest("/Environments");

            request.AddJsonBody(envs);
            request.Method = Method.PUT;

            try
            {
                //var response = await client.PutAsync<Tuple<ObjectOperationResponse, List<Environment>>>(request);
                var response = await client.ExecuteAsync(request);

                var obj = JsonConvert.DeserializeObject<Tuple<ObjectOperationResponse, List<Environment>>>(response.Content);

                return obj;

            }
            catch (Exception ex)
            {
                var resp = new ObjectOperationResponse()
                {
                    Status = ObjectOperationStatus.Error,
                    Message = ex.Message
                };
                return new Tuple<ObjectOperationResponse, List<Environment>>(resp, envs);
            }

            return null;
        }
    }

 }