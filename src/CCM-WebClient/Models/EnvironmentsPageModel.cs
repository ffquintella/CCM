using System;
using System.Collections.Generic;
using Blazorise.DataGrid;
using CCM_WebClient.Translation;
using Domain;
using Domain.Protocol;
//using Microsoft.AspNetCore.Blazor.Components;
using Microsoft.AspNetCore.Components;
using Services;
using Environment = Domain.Environment;

namespace CCM_WebClient.Models
{
    public class EnvironmentsPageModel: BaseModel
    {
        
        [Inject] EnvironmentService EnvironmentService { get; set; }
        protected List<Environment> Environments { get; set; }
        
        protected Environment SelectedEnvironment { get; set; }
        
        protected override void OnInitialized()
        {
            Environments = new List<Environment>();
     
                        
            var envs = EnvironmentService.GetAll();
            if (envs == null) Environments = new List<Environment>();
            else Environments = envs;

        }

        public Action<CancellableRowChange<Environment, Dictionary<string, object>>> InsertingAction { get; set; }


        public EnvironmentsPageModel()
        {
            InsertingAction = OnInserting;
        }
        
        protected void OnInserting(CancellableRowChange<Environment, Dictionary<string, object>> crc)
        {

        }
        
        protected void OnRowInserted(SavedRowItem<Environment, Dictionary<string, object>> e)
        {
            // The user is new 

        }
        
        protected void OnRemoving(CancellableRowChange<Environment> crc)
        {
            // Called when the row is being removed
        }
        
        protected void OnRowUpdated( SavedRowItem<Environment, Dictionary<string, object>> e )
        {
            // Called when the row is being updated
        }

        protected async void SaveEnvironments()
        {
            var result = await EnvironmentService.SaveAsync(Environments);

            if (result.Item1.Status == ObjectOperationStatus.Updated)
            {
                Environments = result.Item2;
                ShowInfo(T._("Confirmation"), T._("Saved successfully") + " !");
                StateHasChanged();
            }
            else
            {
                ShowError(T._("Error"), result.Item1.Message);
            }
        }
    }
}