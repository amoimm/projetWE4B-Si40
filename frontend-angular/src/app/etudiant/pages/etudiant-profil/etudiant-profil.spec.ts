import { ComponentFixture, TestBed } from '@angular/core/testing';
import { EtudiantProfilComponent } from './etudiant-profil';

describe('EtudiantProfilComponent', () => {
  let component: EtudiantProfilComponent;
  let fixture: ComponentFixture<EtudiantProfilComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EtudiantProfilComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(EtudiantProfilComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
